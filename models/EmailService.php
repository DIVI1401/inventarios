<?php

require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../config/Database.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envío de alertas por correo.
 * - Si hay BREVO_API_KEY usa la API HTTPS de Brevo (funciona en Render, que bloquea SMTP).
 * - Si no, usa SMTP con PHPMailer (útil en local).
 * Ver config/mail_config.example.php.
 */
class EmailService {

    private static ?string $ultimoError = null;

    public static function ultimoError(): ?string {
        return self::$ultimoError;
    }

    private static function config(): array {
        $ruta = __DIR__ . '/../config/mail_config.php';
        return file_exists($ruta) ? require $ruta : require __DIR__ . '/../config/mail_config.example.php';
    }

    public static function estaConfigurado(): bool {
        $c = self::config();
        return ($c['brevoApiKey'] ?? '') !== '' || (($c['usuario'] ?? '') !== '' && ($c['password'] ?? '') !== '');
    }

    /** MAIL_TO (separado por comas) o, si está vacío, los administradores activos. */
    private static function destinatarios(array $c): array {
        $lista = array_filter(array_map('trim', explode(',', $c['correoDestino'] ?? '')));
        if (empty($lista)) {
            $stmt = Database::conectar()->query(
                "SELECT u.correo FROM usuarios u
                 INNER JOIN roles r ON r.idRol = u.idRol
                 WHERE r.nombre = 'Administrador' AND u.estado = 1"
            );
            $lista = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return array_values(array_filter($lista, fn($c) => filter_var($c, FILTER_VALIDATE_EMAIL)));
    }

    /**
     * Envía un correo HTML. $adjuntos = [['ruta' => '/tmp/x.xls', 'nombre' => 'reporte.xls'], ...]
     */
    public static function enviar(string $asunto, string $html, array $adjuntos = []): bool {
        self::$ultimoError = null;
        $c = self::config();

        if (!self::estaConfigurado()) {
            self::$ultimoError = 'El correo no está configurado: el servidor no recibe la variable de entorno BREVO_API_KEY. '
                . 'En Render → Environment agrégala con ese nombre exacto y guarda con "Save, rebuild, and deploy".';
            error_log('[correo] ' . self::$ultimoError);
            return false;
        }
        if (($c['brevoApiKey'] ?? '') !== '' && !filter_var($c['remitente'] ?? '', FILTER_VALIDATE_EMAIL)) {
            self::$ultimoError = 'Falta la variable de entorno MAIL_FROM (el correo remitente verificado en Brevo).';
            error_log('[correo] ' . self::$ultimoError);
            return false;
        }

        $para = self::destinatarios($c);
        if (empty($para)) {
            self::$ultimoError = 'No hay destinatarios: configura MAIL_TO o registra un administrador con correo válido.';
            error_log('[correo] ' . self::$ultimoError);
            return false;
        }

        try {
            return ($c['brevoApiKey'] ?? '') !== ''
                ? self::enviarPorBrevo($c, $para, $asunto, $html, $adjuntos)
                : self::enviarPorSmtp($c, $para, $asunto, $html, $adjuntos);
        } catch (\Throwable $e) {
            self::$ultimoError = $e->getMessage();
            error_log('[correo] ' . $e->getMessage());
            return false;
        }
    }

    private static function enviarPorBrevo(array $c, array $para, string $asunto, string $html, array $adjuntos): bool {
        $datos = [
            'sender' => ['email' => $c['remitente'], 'name' => $c['nombreRemitente']],
            'to' => array_map(fn($correo) => ['email' => $correo], $para),
            'subject' => $asunto,
            'htmlContent' => $html,
        ];
        foreach ($adjuntos as $a) {
            $datos['attachment'][] = ['name' => $a['nombre'], 'content' => base64_encode(file_get_contents($a['ruta']))];
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: ' . $c['brevoApiKey'],
            ],
            CURLOPT_POSTFIELDS => json_encode($datos),
        ]);
        $respuesta = curl_exec($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($codigo >= 200 && $codigo < 300) {
            return true;
        }
        throw new \RuntimeException("Brevo respondió HTTP {$codigo}: " . ($errorCurl ?: (string)$respuesta));
    }

    private static function enviarPorSmtp(array $c, array $para, string $asunto, string $html, array $adjuntos): bool {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $c['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $c['usuario'];
        $mail->Password = $c['password'];
        $mail->SMTPSecure = (int)$c['puerto'] === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $c['puerto'];
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 15;

        $mail->setFrom(($c['remitente'] ?? '') ?: $c['usuario'], $c['nombreRemitente'] ?? 'AetherStock');
        foreach ($para as $correo) {
            $mail->addAddress($correo);
        }
        foreach ($adjuntos as $a) {
            $mail->addAttachment($a['ruta'], $a['nombre']);
        }
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $html;
        return $mail->send();
    }

    private static function e($texto): string {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }

    // Notifica un solo insumo con stock bajo (se llama automáticamente tras una merma, transferencia o venta)
    public static function notificarStockBajo(string $nombreInsumo, string $nombreAlmacen, float $cantidadActual, float $stockMinimo): bool {
        $html = "
            <h2>Alerta de inventario</h2>
            <p>El insumo <strong>" . self::e($nombreInsumo) . "</strong> en la sucursal <strong>" . self::e($nombreAlmacen) . "</strong>
            está en o por debajo del stock mínimo.</p>
            <ul>
                <li>Stock actual: <strong>" . self::e($cantidadActual) . "</strong></li>
                <li>Stock mínimo: <strong>" . self::e($stockMinimo) . "</strong></li>
            </ul>
            <p>Se recomienda generar una compra a proveedor lo antes posible.</p>";
        return self::enviar("⚠️ Alerta de stock bajo: {$nombreInsumo}", $html);
    }

    // Resumen de TODOS los insumos con stock bajo (botón manual desde Alertas)
    public static function notificarResumenStockBajo(array $items): bool {
        if (empty($items)) {
            self::$ultimoError = 'No hay insumos con stock bajo para notificar.';
            return false;
        }

        $filas = '';
        foreach ($items as $i) {
            $filas .= '<tr><td>' . self::e($i['insumo_nombre']) . '</td><td>' . self::e($i['almacen_nombre']) . '</td><td>'
                . self::e($i['stock_actual']) . '</td><td>' . self::e($i['stock_minimo']) . '</td></tr>';
        }
        $html = "
            <h2>Resumen de insumos con stock bajo</h2>
            <table border='1' cellpadding='6' style='border-collapse:collapse'>
                <tr><th>Insumo</th><th>Almacén</th><th>Stock actual</th><th>Stock mínimo</th></tr>
                {$filas}
            </table>";
        return self::enviar("⚠️ Resumen de stock bajo — " . count($items) . " insumo(s)", $html);
    }

    public static function enviarReporteInventarioConAdjunto(string $rutaArchivo): bool {
        $html = "<p>Adjunto el reporte de inventario completo de AetherStock, generado el " . date('d/m/Y \a \l\a\s H:i') . ".</p>";
        return self::enviar(
            "📊 Reporte de inventario — " . date('d/m/Y'),
            $html,
            [['ruta' => $rutaArchivo, 'nombre' => "reporte_inventario_" . date('Y-m-d') . ".xls"]]
        );
    }
}
