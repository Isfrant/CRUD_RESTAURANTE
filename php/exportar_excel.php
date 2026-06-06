<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php'); exit;
}

require_once __DIR__ . '/../config/conexion.php';

$pdo  = getConexion();
$stmt = $pdo->query(
    'SELECT i.id, i.nombre, c.nombre AS categoria, i.stock_actual, i.stock_minimo,
            i.precio_unitario,
            ROUND(i.stock_actual * i.precio_unitario, 2) AS valor_total,
            i.fecha_vencimiento, i.imagen_ruta,
            CASE
                WHEN i.stock_actual = 0             THEN "AGOTADO"
                WHEN i.stock_actual < i.stock_minimo THEN "BAJO STOCK"
                ELSE "OK"
            END AS estado
     FROM insumos i
     LEFT JOIN categorias c ON i.categoria_id = c.id
     ORDER BY c.nombre, i.nombre'
);
$rows = $stmt->fetchAll();

 exportCsv($rows);


function exportCsv(array $rows): void {
   
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inventario_' . date('Ymd_Hi') . '.csv"');
    
   
    $out = fopen('php://output', 'w');

    fputs($out, "\xEF\xBB\xBF");
    

    fputcsv($out, ['ID', 'Nombre', 'Categoría', 'Stock Actual', 'Stock Mínimo', 'Estado', 'Precio Unitario', 'Valor Total', 'Vencimiento'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'], $r['nombre'], $r['categoria'],
            $r['stock_actual'], $r['stock_minimo'], $r['estado'],
            $r['precio_unitario'], $r['valor_total'],
            $r['fecha_vencimiento'] ?: '—'
        ], ';');
    }
    fclose($out);
}
