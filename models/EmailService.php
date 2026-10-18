<?php

require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    private static function crearMailer(): PHPMailer {
        $config = require __DIR__ . '/../config/mail_config.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['usuario'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['puerto'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('notificaciones@aetherstock.com', 'AetherStock');
        $mail->addAddress($config['correoDestino']);

        return $mail;
    }

    // Notifica un solo insumo con stock bajo (se llama automáticamente tras una merma o transferencia)
    public static function notificarStockBajo(string $nombreInsumo, string $nombreAlmacen, float $cantidadActual, float $stockMinimo): bool {
        try {
            $mail = self::crearMailer();
            $mail->isHTML(true);
            $mail->Subject = "⚠️ Alerta de stock bajo: {$nombreInsumo}";
            $mail->Body = "
                <h2>Alerta de inventario</h2>
                <p>El insumo <strong>{$nombreInsumo}</strong> en la sucursal <strong>{$nombreAlmacen}</strong> 
                está por debajo del stock mínimo.</p>
                <ul>
                    <li>Stock actual: <strong>{$cantidadActual}</strong></li>
                    <li>Stock mínimo: <strong>{$stockMinimo}</strong></li>
                </ul>
                <p>Se recomienda generar una compra a proveedor lo antes posible.</p>
            ";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo de alerta: " . $e->getMessage());
            return false;
        }
    }

    // Notifica un resumen de TODOS los insumos con stock bajo (botón manual desde Alertas)
    public static function notificarResumenStockBajo(array $items): bool {
        if (empty($items)) return false;

        try {
            $mail = self::crearMailer();
            $mail->isHTML(true);
            $mail->Subject = "⚠️ Resumen de stock bajo — " . count($items) . " insumo(s)";

            $filas = '';
            foreach ($items as $i) {
                $filas .= "<tr>
                    <td>{$i['insumo_nombre']}</td>
                    <td>{$i['almacen_nombre']}</td>
                    <td>{$i['stock_actual']}</td>
                    <td>{$i['stock_minimo']}</td>
                </tr>";
            }

            $mail->Body = "
                <h2>Resumen de insumos con stock bajo</h2>
                <table border='1' cellpadding='6'>
                    <tr><th>Insumo</th><th>Almacén</th><th>Stock actual</th><th>Stock mínimo</th></tr>
                    {$filas}
                </table>
            ";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando resumen de alertas: " . $e->getMessage());
            return false;
        }
    }

    public static function enviarReporteInventarioConAdjunto(string $rutaArchivo): bool {
        try {
            $mail = self::crearMailer();
            $mail->isHTML(true);
            $mail->Subject = "📊 Reporte diario de inventario — " . date('d/m/Y');
            $mail->Body = "<p>Adjunto el reporte de inventario completo de AetherStock, generado automáticamente el " . date('d/m/Y \a \l\a\s H:i') . ".</p>";
            $mail->addAttachment($rutaArchivo, "reporte_inventario_" . date('Y-m-d') . ".xls");
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando reporte de inventario: " . $e->getMessage());
            return false;
        }
    }
}