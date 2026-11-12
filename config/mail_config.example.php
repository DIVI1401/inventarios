<?php

/*
 * Configuración de correo. En Render todo se lee de variables de entorno.
 *
 * Opción A (recomendada, funciona en Render): API HTTPS de Brevo (brevo.com, gratis 300 correos/día)
 *   BREVO_API_KEY = clave "xkeysib-..." (Brevo → SMTP & API → API Keys)
 *   MAIL_FROM     = correo remitente verificado en Brevo (Senders, domains & IPs → Senders)
 *
 * Opción B (solo local): SMTP con PHPMailer (Gmail con contraseña de aplicación, Mailtrap, etc.)
 *   Render bloquea los puertos SMTP en el plan gratuito, por eso allá se usa la opción A.
 *
 * MAIL_TO: destinatarios separados por coma. Si se deja vacío, se envía a todos los
 * administradores activos registrados en el sistema.
 */
// Lee una variable de entorno sin espacios ni saltos de línea sobrantes (comunes al pegar la clave)
$env = static function (string $clave, string $porDefecto = ''): string {
    $valor = getenv($clave);
    if ($valor === false) {
        $valor = $_SERVER[$clave] ?? $_ENV[$clave] ?? '';
    }
    $valor = trim((string)$valor);
    return $valor !== '' ? $valor : $porDefecto;
};

return [
    'brevoApiKey'   => $env('BREVO_API_KEY'),
    'remitente'     => $env('MAIL_FROM'),
    'nombreRemitente' => $env('MAIL_FROM_NAME', 'AetherStock'),

    'host'     => $env('MAIL_HOST', 'smtp.gmail.com'),
    'usuario'  => $env('MAIL_USER'),
    'password' => $env('MAIL_PASSWORD'),
    'puerto'   => (int)$env('MAIL_PORT', '587'),

    'correoDestino' => $env('MAIL_TO'),
];
