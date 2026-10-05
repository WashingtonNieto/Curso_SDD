<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Installer\AccountRequest;
use App\Http\Requests\Installer\DatabaseRequest;
use App\Http\Requests\Installer\PhotoRequest;
use App\Http\Requests\Installer\PublicDataRequest;
use App\Http\Requests\Installer\ScheduleRequest;
use App\Http\Requests\Installer\ThemeRequest;
use App\Models\AvailabilitySetting;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\HtmlSanitizer;
use App\Services\ImageUploader;
use App\Services\Installer\DatabaseInstaller;
use App\Services\Installer\InstallerProgress;
use App\Services\Installer\RequirementsChecker;
use App\Services\SettingsService;
use App\Services\ThemeManager;
use App\Support\Installation;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InstallerController extends Controller
{
    private const WEEKDAYS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    private const PLAN_NAMES = ['online' => 'Sesión online', 'presencial' => 'Sesión presencial'];

    public function __construct(
        private readonly SettingsService $settings,
        private readonly InstallerProgress $progress,
        private readonly DatabaseInstaller $database,
        private readonly AvailabilityService $availability,
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('installer.show', $this->progress->firstPending());
    }

    public function show(string $step): Response
    {
        return response()->view('installer.steps.'.$step, $this->layoutData($step) + $this->stepData($step));
    }

    public function requirements(RequirementsChecker $checker): RedirectResponse
    {
        if (! $checker->passes()) {
            return back()->withErrors(['requirements' => 'Tu servidor todavía no cumple todos los requisitos. Revisa los elementos marcados en rojo.']);
        }

        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        return $this->complete('requisitos');
    }

    public function database(DatabaseRequest $request): Response
    {
        @set_time_limit(180);
        $credentials = $request->validated();

        try {
            $this->database->install($credentials);
        } catch (RuntimeException $e) {
            return back()->withInput($request->except('db_password'))->withErrors(['database' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput($request->except('db_password'))->withErrors([
                'database' => 'La base de datos se ha creado, pero no se han podido preparar las tablas. Detalle técnico: '.$e->getMessage(),
            ]);
        }

        $this->progress->markCompleted('base-de-datos');

        $response = response()->view('installer.steps.base-de-datos-lista', $this->layoutData('base-de-datos') + [
            'databaseName' => $credentials['db_database'],
        ]);

        $request->session()->save();
        $this->database->writeEnvironment($credentials);

        return $response;
    }

    public function account(AccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::query()->first() ?? new User;

        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $this->complete('cuenta');
    }

    public function publicData(PublicDataRequest $request, HtmlSanitizer $sanitizer): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $sanitizer) {
            $this->settings->many([
                'site.public_name' => $data['public_name'],
                'site.slogan' => $data['slogan'] ?? null,
                'site.license_number' => $data['license_number'] ?? null,
                'site.booking_phone' => $data['booking_phone'],
                'site.booking_email' => $data['booking_email'],
                'site.whatsapp' => $data['whatsapp'] ?? null,
                'site.address' => $data['address'] ?? null,
                'site.city' => $data['city'] ?? null,
                'site.about' => $sanitizer->clean($data['about'] ?? null),
            ]);

            $this->replaceSpecialties($data['specialties'] ?? []);
            $this->replaceServices($data['services'] ?? []);
            $this->replacePlans($data['plans'] ?? []);
        });

        return $this->complete('datos-publicos');
    }

    public function schedule(ScheduleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach (AvailabilitySetting::MODALITIES as $modality) {
                $data = $request->validated($modality);

                AvailabilitySetting::updateOrCreate(['modality' => $modality], [
                    'session_duration' => (int) $data['session_duration'],
                    'break_enabled' => (bool) ($data['break_enabled'] ?? false),
                    'break_minutes' => (int) ($data['break_minutes'] ?? 10),
                    'day_start' => $data['day_start'].':00',
                    'day_end' => $data['day_end'].':00',
                    'needs_review' => false,
                ]);

                $this->availability->fillWeekdays($modality, $data['weekdays'] ?? []);

                Plan::where('modality', $modality)->update(['duration_label' => $data['session_duration'].' minutos']);
            }
        });

        return $this->complete('horarios');
    }

    public function photo(PhotoRequest $request, ImageUploader $uploader): RedirectResponse
    {
        if ($request->hasFile('photo')) {
            $this->settings->set('site.photo', $uploader->store($request->file('photo'), 'perfil', $this->settings->get('site.photo')));
        }

        return $this->complete('foto');
    }

    public function theme(ThemeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->settings->many([
            'theme.active' => $data['theme'],
            'theme.mode' => $data['theme_mode'],
            'installer.demo_content' => (bool) ($data['demo_content'] ?? false),
        ]);

        return $this->complete('tema');
    }

    public function finish(Request $request): RedirectResponse
    {
        $user = User::query()->first();

        if (! $user) {
            $this->progress->forget('cuenta');

            return redirect()->route('installer.show', 'cuenta')
                ->withErrors(['account' => 'Antes de terminar necesitas crear tu cuenta de acceso.']);
        }

        if ($this->settings->get('installer.demo_content')) {
            @set_time_limit(120);
            Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
        }

        $this->settings->forget('installer.demo_content');
        Installation::markInstalled();

        $this->progress->reset();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel.home')->with('toast', [
            'type' => 'success',
            'message' => '¡Instalación completada! Te damos la bienvenida a tu panel, '.$user->first_name.'.',
        ]);
    }

    private function complete(string $step): RedirectResponse
    {
        $this->progress->markCompleted($step);

        return redirect()->route('installer.show', $this->progress->next($step));
    }

    private function layoutData(string $step): array
    {
        return [
            'steps' => InstallerProgress::STEPS,
            'currentStep' => $step,
            'stepNumber' => $this->progress->position($step),
            'totalSteps' => count(InstallerProgress::STEPS),
            'completedSteps' => $this->progress->completed(),
            'previousStep' => $this->progress->previous($step),
        ];
    }

    private function stepData(string $step): array
    {
        return match ($step) {
            'requisitos' => ['checks' => app(RequirementsChecker::class)->checks()],
            'base-de-datos' => ['defaults' => $this->databaseDefaults()],
            'cuenta' => ['user' => User::query()->first()],
            'datos-publicos' => $this->publicDataDefaults(),
            'horarios' => $this->scheduleDefaults(),
            'foto' => ['photoUrl' => public_storage_url($this->settings->get('site.photo'))],
            'tema' => [
                'themes' => app(ThemeManager::class)->all(),
                'activeTheme' => $this->settings->get('theme.active', config('psicocms.default_theme')),
                'activeMode' => $this->settings->get('theme.mode', 'landing'),
                'demoContent' => (bool) $this->settings->get('installer.demo_content', false),
            ],
            'finalizar' => $this->summary(),
            default => [],
        };
    }

    private function databaseDefaults(): array
    {
        return [
            'db_host' => config('database.connections.mysql.host', '127.0.0.1'),
            'db_port' => config('database.connections.mysql.port', '3306'),
            'db_database' => config('database.connections.mysql.database', 'psicocms'),
            'db_username' => config('database.connections.mysql.username', 'root'),
        ];
    }

    private function publicDataDefaults(): array
    {
        $user = User::query()->first();
        $plans = Plan::query()->get()->keyBy('modality');

        return [
            'site' => [
                'public_name' => $this->settings->get('site.public_name', $user?->full_name),
                'slogan' => $this->settings->get('site.slogan'),
                'license_number' => $this->settings->get('site.license_number'),
                'booking_phone' => $this->settings->get('site.booking_phone', $user?->phone),
                'booking_email' => $this->settings->get('site.booking_email', $user?->email),
                'whatsapp' => $this->settings->get('site.whatsapp', $user?->phone),
                'address' => $this->settings->get('site.address'),
                'city' => $this->settings->get('site.city'),
                'about' => $this->settings->get('site.about'),
            ],
            'specialties' => Specialty::query()->ordered()->pluck('name')->all(),
            'services' => Service::query()->ordered()->get()
                ->map(fn (Service $service) => ['title' => $service->title, 'description' => $service->excerpt])
                ->all(),
            'plans' => collect(self::PLAN_NAMES)->map(fn ($name, $modality) => [
                'price' => $plans[$modality]->price ?? null,
                'description' => $plans[$modality]->description ?? null,
            ])->all(),
        ];
    }

    private function scheduleDefaults(): array
    {
        $schedule = [];

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            $setting = AvailabilitySetting::forModality($modality);
            $weekdays = $this->availability->weekdaysWithSlots($modality);

            $schedule[$modality] = [
                'session_duration' => $setting->session_duration,
                'break_enabled' => $setting->break_enabled,
                'break_minutes' => $setting->break_minutes,
                'day_start' => substr($setting->day_start, 0, 5),
                'day_end' => substr($setting->day_end, 0, 5),
                'weekdays' => $weekdays !== [] || $this->progress->isCompleted('horarios') ? $weekdays : [1, 2, 3, 4, 5],
            ];
        }

        return [
            'schedule' => $schedule,
            'presets' => config('psicocms.session_duration_presets'),
            'weekdayNames' => self::WEEKDAYS,
        ];
    }

    private function summary(): array
    {
        return [
            'user' => User::query()->first(),
            'publicName' => $this->settings->get('site.public_name'),
            'specialtiesCount' => Specialty::count(),
            'servicesCount' => Service::count(),
            'plans' => Plan::query()->ordered()->get(),
            'scheduleSummary' => collect(AvailabilitySetting::MODALITIES)->mapWithKeys(function (string $modality) {
                $setting = AvailabilitySetting::forModality($modality);

                return [$modality => [
                    'duration' => $setting->session_duration,
                    'break' => $setting->effectiveBreak(),
                    'from' => substr($setting->day_start, 0, 5),
                    'to' => substr($setting->day_end, 0, 5),
                    'days' => count($this->availability->weekdaysWithSlots($modality)),
                ]];
            })->all(),
            'photoUrl' => public_storage_url($this->settings->get('site.photo')),
            'theme' => app(ThemeManager::class)->find($this->settings->get('theme.active')),
            'themeMode' => config('psicocms.theme_modes.'.$this->settings->get('theme.mode', 'landing')),
            'demoContent' => (bool) $this->settings->get('installer.demo_content', false),
        ];
    }

    private function replaceSpecialties(array $names): void
    {
        Specialty::query()->delete();

        foreach ($names as $order => $name) {
            Specialty::create(['name' => $name, 'slug' => $this->uniqueSlug($name, Specialty::class), 'sort_order' => $order]);
        }
    }

    private function replaceServices(array $services): void
    {
        Service::query()->delete();

        foreach ($services as $order => $service) {
            $description = trim((string) ($service['description'] ?? '')) ?: null;

            Service::create([
                'title' => $service['title'],
                'slug' => $this->uniqueSlug($service['title'], Service::class),
                'icon' => 'fa-solid fa-heart',
                'excerpt' => $description,
                'content' => $description ? '<p>'.e($description).'</p>' : null,
                'sort_order' => $order,
            ]);
        }
    }

    private function replacePlans(array $plans): void
    {
        Plan::query()->delete();

        foreach (self::PLAN_NAMES as $modality => $name) {
            $price = $plans[$modality]['price'] ?? null;

            if ($price === null || $price === '') {
                continue;
            }

            Plan::create([
                'name' => $name,
                'modality' => $modality,
                'price' => $price,
                'duration_label' => AvailabilitySetting::forModality($modality)->session_duration.' minutos',
                'description' => $plans[$modality]['description'] ?? null,
                'sort_order' => $modality === 'online' ? 0 : 1,
            ]);
        }
    }

    private function uniqueSlug(string $text, string $model): string
    {
        $base = Str::slug($text) ?: Str::lower(Str::random(6));
        $slug = $base;
        $suffix = 2;

        while ($model::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
