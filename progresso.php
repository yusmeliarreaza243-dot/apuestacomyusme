<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/sorteos.php';

try {
    $sorteos = obtenerSorteosConProgresso($pdo);

    $resultado = array_map(function ($s) {
        return [
            'sorteo_id'  => $s['id'],
            'vendidos'   => $s['vendidos'],
            'total'      => $s['total'],
            'porcentaje' => $s['porcentaje'],
        ];
    }, $sorteos);

    echo json_encode($resultado);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Não foi possível calcular o progresso.']);
}
