<?php

function requiereLogin(): void {
    if (empty($_SESSION['idUsuario'])) {
        header("Location: index.php?action=login");
        exit;
    }
}

function requiereRol(array $rolesPermitidos): void {
    requiereLogin();

    if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
        http_response_code(403);
        echo "No tienes permiso para acceder a esta sección.";
        exit;
    }
}