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
return [
    'brevoApiKey'   => getenv('BREVO_API_KEY') ?: '',
    'remitente'     => getenv('MAIL_FROM') ?: '',
    'nombreRemitente' => getenv('MAIL_FROM_NAME') ?: 'AetherStock',

    'host'     => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'usuario'  => getenv('MAIL_USER') ?: '',
    'password' => getenv('MAIL_PASSWORD') ?: '',
    'puerto'   => (int)(getenv('MAIL_PORT') ?: 587),

    'correoDestino' => getenv('MAIL_TO') ?: '',
];
