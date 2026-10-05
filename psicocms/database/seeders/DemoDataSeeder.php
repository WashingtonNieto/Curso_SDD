<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ClinicalEntry;
use App\Models\Faq;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Specialty;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedServices();
        $this->seedSpecialties();
        $this->seedPlans();
        $this->seedFaqs();
        $this->seedBlog();
        $this->seedPatients();
    }

    private function seedServices(): void
    {
        if (Service::exists()) {
            return;
        }

        $services = [
            ['Terapia individual', 'fa-solid fa-user', 'Un espacio seguro y confidencial para trabajar lo que te preocupa a tu ritmo.'],
            ['Terapia de pareja', 'fa-solid fa-heart', 'Mejorad la comunicación y recuperad la complicidad en vuestra relación.'],
            ['Terapia online', 'fa-solid fa-laptop', 'La misma calidad de atención desde la comodidad de tu casa.'],
            ['Talleres de bienestar', 'fa-solid fa-people-group', 'Sesiones grupales para aprender a gestionar el estrés y las emociones.'],
        ];

        foreach ($services as $order => [$title, $icon, $excerpt]) {
            Service::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'icon' => $icon,
                'excerpt' => $excerpt,
                'content' => '<p>'.$excerpt.'</p>',
                'sort_order' => $order,
            ]);
        }
    }

    private function seedSpecialties(): void
    {
        if (Specialty::exists()) {
            return;
        }

        $specialties = [
            ['Ansiedad', 'fa-solid fa-wind'],
            ['Depresión', 'fa-solid fa-cloud-sun'],
            ['Autoestima', 'fa-solid fa-seedling'],
            ['Duelo', 'fa-solid fa-dove'],
            ['Estrés laboral', 'fa-solid fa-briefcase'],
            ['Relaciones de pareja', 'fa-solid fa-people-arrows'],
        ];

        foreach ($specialties as $order => [$name, $icon]) {
            Specialty::create(['name' => $name, 'slug' => Str::slug($name), 'icon' => $icon, 'sort_order' => $order]);
        }
    }

    private function seedPlans(): void
    {
        if (Plan::exists()) {
            return;
        }

        Plan::create([
            'name' => 'Sesión online',
            'modality' => 'online',
            'price' => 150000,
            'duration_label' => '50 minutos',
            'description' => 'Videollamada segura desde cualquier lugar.',
            'features' => "Videollamada segura\nMaterial de apoyo por email\nHorario flexible",
            'sort_order' => 0,
        ]);

        Plan::create([
            'name' => 'Sesión presencial',
            'modality' => 'presencial',
            'price' => 180000,
            'duration_label' => '50 minutos',
            'description' => 'En consulta, en un ambiente cálido y tranquilo.',
            'features' => "Consulta privada\nMaterial de apoyo\nSeguimiento personalizado",
            'is_featured' => true,
            'sort_order' => 1,
        ]);
    }

    private function seedFaqs(): void
    {
        if (Faq::exists()) {
            return;
        }

        $faqs = [
            ['¿Cuánto dura cada sesión?', 'Cada sesión dura aproximadamente 50 minutos.'],
            ['¿Cuántas sesiones voy a necesitar?', 'Depende de cada persona y de su objetivo. En la primera sesión valoraremos juntas un plan orientativo.'],
            ['¿La terapia online es igual de eficaz?', 'Sí. Numerosos estudios muestran que la terapia online es tan eficaz como la presencial para la mayoría de los casos.'],
            ['¿Qué pasa si tengo que cancelar una cita?', 'Puedes cancelarla o cambiarla avisando con al menos 24 horas de antelación.'],
            ['¿Es confidencial todo lo que hablemos?', 'Por supuesto. Todo lo que compartas en consulta está protegido por el secreto profesional.'],
        ];

        foreach ($faqs as $order => [$question, $answer]) {
            Faq::create(['question' => $question, 'answer' => '<p>'.$answer.'</p>', 'sort_order' => $order]);
        }
    }

    private function seedBlog(): void
    {
        if (BlogPost::exists()) {
            return;
        }

        $posts = [
            ['5 técnicas sencillas para calmar la ansiedad', 'ansiedad-y-estres', 'blog1.jpg'],
            ['Cómo mejorar la comunicación en pareja', 'relaciones-y-pareja', 'blog2.jpg'],
            ['Autoestima: aprender a tratarte con amabilidad', 'autoestima-y-crecimiento-personal', 'blog3.jpg'],
        ];

        foreach ($posts as $index => [$title, $categorySlug, $image]) {
            BlogPost::create([
                'blog_category_id' => BlogCategory::where('slug', $categorySlug)->value('id'),
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => 'Pequeños cambios que marcan una gran diferencia en tu bienestar emocional.',
                'content' => '<p>Este es un artículo de ejemplo. Puedes editarlo o eliminarlo desde el panel de gestión del blog.</p><h2>Primer paso</h2><p>Dedica unos minutos al día a observar cómo te sientes, sin juzgarte.</p><h2>Segundo paso</h2><p>Respira de forma lenta y profunda: inspira en cuatro tiempos y espira en seis.</p>',
                'image_path' => $this->copyDemoImage($image),
                'status' => 'publicado',
                'published_at' => now()->subDays(($index + 1) * 9),
                'meta_description' => 'Artículo de ejemplo del blog de psicología.',
            ]);
        }
    }

    private function copyDemoImage(string $file): ?string
    {
        $source = database_path('seeders/demo/'.$file);

        if (! is_file($source)) {
            return null;
        }

        $path = 'uploads/blog/demo-'.$file;
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    private function seedPatients(): void
    {
        if (Patient::exists()) {
            return;
        }

        $patients = Patient::factory()->count(14)->create();
        $prices = Plan::pluck('price', 'modality');
        $today = CarbonImmutable::today();
        $taken = [];

        foreach (range(-56, 14) as $offset) {
            $day = $today->addDays($offset);

            if ($day->isWeekend()) {
                continue;
            }

            foreach ([9, 10, 11, 12, 13] as $hour) {
                if (random_int(1, 100) > 45) {
                    continue;
                }

                $start = $day->setTime($hour, 0);
                $key = $start->format('Y-m-d H:i');

                if (isset($taken[$key])) {
                    continue;
                }

                $taken[$key] = true;
                $modality = random_int(0, 1) ? 'online' : 'presencial';
                $isPast = $start->isPast();

                Appointment::create([
                    'patient_id' => $patients->random()->id,
                    'modality' => $modality,
                    'starts_at' => $start,
                    'ends_at' => $start->addMinutes(50),
                    'break_minutes' => $modality === 'online' ? 10 : 0,
                    'status' => $isPast
                        ? collect(['completada', 'completada', 'completada', 'completada', 'cancelada', 'no_asistio'])->random()
                        : collect(['confirmada', 'confirmada', 'pendiente'])->random(),
                    'source' => collect(array_keys(Appointment::SOURCES))->random(),
                    'reason' => 'Sesión de seguimiento',
                    'price' => $prices[$modality] ?? 150000,
                    'seen_at' => now(),
                ]);
            }
        }

        foreach ($patients->take(8) as $patient) {
            $appointments = $patient->appointments()->where('status', 'completada')->orderBy('starts_at')->get();

            foreach ($appointments->take(3) as $appointment) {
                ClinicalEntry::factory()->create([
                    'patient_id' => $patient->id,
                    'appointment_id' => $appointment->id,
                    'session_date' => $appointment->starts_at->toDateString(),
                ]);
            }
        }
    }
}
