<?php

namespace Database\Seeders;

use App\Models\AvailabilitySetting;
use App\Models\BlogCategory;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BaseDataSeeder extends Seeder
{
    public const BLOG_CATEGORIES = [
        'Ansiedad y estrés' => 'Claves para entender y gestionar la ansiedad y el estrés del día a día.',
        'Salud mental' => 'Información rigurosa y cercana sobre salud mental.',
        'Relaciones y pareja' => 'Comunicación, vínculos afectivos y vida en pareja.',
        'Autoestima y crecimiento personal' => 'Herramientas para quererte mejor y crecer como persona.',
        'Bienestar emocional' => 'Hábitos y recursos para cuidar tus emociones.',
        'Terapia y psicología' => 'Cómo funciona la terapia y qué puedes esperar de ella.',
    ];

    public function run(SettingsService $settings): void
    {
        foreach (self::BLOG_CATEGORIES as $name => $description) {
            BlogCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'description' => $description]);
        }

        foreach (AvailabilitySetting::MODALITIES as $modality) {
            AvailabilitySetting::firstOrCreate(['modality' => $modality], [
                'session_duration' => 50,
                'break_enabled' => $modality === 'online',
                'break_minutes' => 10,
                'day_start' => '09:00:00',
                'day_end' => '14:00:00',
            ]);
        }

        $modules = [];
        foreach (array_keys(config('psicocms.modules')) as $module) {
            $modules['modules.'.$module] = true;
        }

        $settings->setIfMissing($modules + [
            'theme.active' => config('psicocms.default_theme'),
            'theme.mode' => 'landing',
            'booking.vacation_mode' => false,
            'mail.enabled' => false,
            'privacy.template' => self::privacyTemplate(),
        ]);
    }

    public static function privacyTemplate(): string
    {
        return <<<'HTML'
<h2>Consentimiento informado y protección de datos personales</h2>
<p>En [fecha], D./Dña. <strong>[nombre_paciente]</strong>, con DNI [dni_paciente], teléfono [telefono_paciente], correo electrónico [email_paciente] y domicilio en [direccion_paciente], declara haber sido informado/a de lo siguiente:</p>
<h3>1. Responsable del tratamiento</h3>
<p>[nombre_psicologa], psicóloga colegiada n.º [num_colegiado], con consulta en [direccion_consulta] y correo electrónico de contacto [email_consulta].</p>
<h3>2. Finalidad</h3>
<p>Los datos personales y de salud facilitados se tratarán exclusivamente para la prestación del servicio de atención psicológica, la gestión de citas, la elaboración y custodia de la historia clínica y, en su caso, la facturación.</p>
<h3>3. Legitimación</h3>
<p>El tratamiento se basa en el consentimiento expreso de la persona interesada y en el cumplimiento de las obligaciones legales aplicables a la profesión sanitaria, conforme al Reglamento (UE) 2016/679 (RGPD) y a la Ley Orgánica 3/2018 de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD).</p>
<h3>4. Conservación</h3>
<p>La historia clínica se conservará durante el tiempo mínimo exigido por la normativa sanitaria vigente. El resto de datos se conservarán mientras se mantenga la relación profesional y, después, durante los plazos legalmente establecidos.</p>
<h3>5. Destinatarios</h3>
<p>Los datos no se cederán a terceros salvo obligación legal. La información compartida en consulta está protegida por el secreto profesional.</p>
<h3>6. Derechos</h3>
<p>Puede ejercer sus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad escribiendo a [email_consulta]. También puede presentar una reclamación ante la Agencia Española de Protección de Datos (www.aepd.es).</p>
<p>Y para que así conste, firma el presente documento.</p>
HTML;
    }
}
