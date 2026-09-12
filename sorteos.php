<?php
/**
 * Devuelve todos los sorteos activos junto con su progreso de venta,
 * calculado en tiempo real a partir de la tabla `numeros`.
 * El porcentaje sube solo: se calcula contando cuántos números de
 * ESE sorteo (y solo ese) están en estado 'vendido'.
 */
function obtenerSorteosConProgresso(PDO $pdo): array
{
    $sql = "
        SELECT
            s.id,
            s.nombre,
            s.fecha_sorteo,
            s.hora_sorteo,
            s.premio,
            s.total_numeros,
            COUNT(n.id) AS numeros_generados,
            SUM(n.estado = 'vendido') AS numeros_vendidos
        FROM sorteos s
        LEFT JOIN numeros n ON n.sorteo_id = s.id
        WHERE s.estado = 'activo'
        GROUP BY s.id, s.nombre, s.fecha_sorteo, s.hora_sorteo, s.premio, s.total_numeros
        ORDER BY s.fecha_sorteo ASC
    ";

    $stmt = $pdo->query($sql);
    $filas = $stmt->fetchAll();

    $sorteos = [];
    foreach ($filas as $r) {
        $totalBase = (int)$r['total_numeros'];
        // Si aún no se corrió el seed de números, evitamos dividir por cero.
        $base      = $totalBase > 0 ? $totalBase : (int)$r['numeros_generados'];
        $vendidos  = (int)$r['numeros_vendidos'];
        $porcentaje = $base > 0 ? (int)round(($vendidos / $base) * 100) : 0;

        $sorteos[] = [
            'id'          => (int)$r['id'],
            'nombre'      => $r['nombre'],
            'fecha'       => $r['fecha_sorteo'],
            'hora'        => $r['hora_sorteo'],
            'premio'      => (float)$r['premio'],
            'vendidos'    => $vendidos,
            'total'       => $base,
            'porcentaje'  => $porcentaje,
        ];
    }

    return $sorteos;
}
