<?php

return [
    'host' => getenv('MAIL_HOST') ?: 'sandbox.smtp.mailtrap.io',
    'usuario' => getenv('MAIL_USER') ?: 'TU_USUARIO',
    'password' => getenv('MAIL_PASSWORD') ?: 'TU_PASSWORD',
    'puerto' => (int)(getenv('MAIL_PORT') ?: 2525),
    'correoDestino' => getenv('MAIL_TO') ?: 'admin@aetherstock.com',
];