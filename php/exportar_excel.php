<?php
// ============================================================
// Exportar Excel – Inventario Completo
// Genera un archivo .xlsx usando PhpSpreadsheet (composer)
// Fallback: CSV compatible con Excel si no está instalado.
// ============================================================
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php'); exit;
}

require_once __DIR__ . '/../config/conexion.php';

// ── Datos ─────────────────────────────────────────────────
$pdo  = getConexion();
// Consulta SQL para obtener TODOS los insumos. Usamos CASE para determinar el 'estado' directamente en SQL.
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

// ── Intentar PhpSpreadsheet ───────────────────────────────
$xlsxPaths = [
    __DIR__ . '/../vendor/autoload.php',
];
$xlsxLoaded = false;
foreach ($xlsxPaths as $p) {
    if (file_exists($p)) { require_once $p; $xlsxLoaded = true; break; }
}

if ($xlsxLoaded && class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    exportXlsx($rows);
} else {
    exportCsv($rows);
}

// ── XLSX con PhpSpreadsheet ───────────────────────────────
function exportXlsx(array $rows): void {
    $spread = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet  = $spread->getActiveSheet();
    $sheet->setTitle('Inventario');

    // Estilos
    $gold     = 'C9A84C';
    $darkBg   = '1A1714';
    $cream    = 'F5F0E8';
    $redFill  = 'C0392B';
    $amberFill= 'D68910';
    $greenFill= '27AE60';

    // Título
    $sheet->mergeCells('A1:J1');
    $sheet->setCellValue('A1', 'RestaurantePRO – Inventario Completo');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $gold]],
        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $darkBg]],
        'alignment' => ['horizontal' => 'center'],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(22);

    // Fecha generación
    $sheet->mergeCells('A2:J2');
    $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i') . '  |  Total registros: ' . count($rows));
    $sheet->getStyle('A2')->applyFromArray([
        'font'      => ['size' => 9, 'color' => ['rgb' => '888888']],
        'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '111111']],
        'alignment' => ['horizontal' => 'center'],
    ]);

    // Encabezados (fila 4)
    $headers = ['#', 'Nombre', 'Categoría', 'Stock Actual', 'Stock Mín.', 'Estado', 'Precio Unit. (COP)', 'Valor Total (COP)', 'Vencimiento', 'Imagen'];
    $cols    = ['A','B','C','D','E','F','G','H','I','J'];
    $widths  = [5, 35, 20, 14, 12, 13, 20, 20, 16, 30];

    foreach ($headers as $k => $h) {
        $cell = $cols[$k] . '4';
        $sheet->setCellValue($cell, $h);
        $sheet->getStyle($cell)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $darkBg]],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $gold]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '886622']]],
        ]);
        $sheet->getColumnDimension($cols[$k])->setWidth($widths[$k]);
    }
    $sheet->getRowDimension(4)->setRowHeight(18);

    // Datos
    foreach ($rows as $i => $row) {
        $r = $i + 5;

        $sheet->setCellValue("A$r", $row['id']);
        $sheet->setCellValue("B$r", $row['nombre']);
        $sheet->setCellValue("C$r", $row['categoria']);
        $sheet->setCellValue("D$r", floatval($row['stock_actual']));
        $sheet->setCellValue("E$r", floatval($row['stock_minimo']));
        $sheet->setCellValue("F$r", $row['estado']);
        $sheet->setCellValue("G$r", floatval($row['precio_unitario']));
        $sheet->setCellValue("H$r", floatval($row['valor_total']));
        $sheet->setCellValue("I$r", $row['fecha_vencimiento'] ?: '—');
        $sheet->setCellValue("J$r", $row['imagen_ruta'] ?: '—');

        // Formato numérico
        $numFmt = '#,##0.00';
        $sheet->getStyle("G$r")->getNumberFormat()->setFormatCode('[$COP-x-euro2] #,##0.00');
        $sheet->getStyle("H$r")->getNumberFormat()->setFormatCode('[$COP-x-euro2] #,##0.00');
        $sheet->getStyle("D$r")->getNumberFormat()->setFormatCode($numFmt);
        $sheet->getStyle("E$r")->getNumberFormat()->setFormatCode($numFmt);

        // Color estado
        $bgRow = ($i % 2 === 0) ? '1E1A16' : '16120E';
        $fgRow = $cream;
        if ($row['estado'] === 'AGOTADO') {
            $sheet->getStyle("F$r")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $redFill]],
            ]);
            $bgRow = '3D1A17';
        } elseif ($row['estado'] === 'BAJO STOCK') {
            $sheet->getStyle("F$r")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $amberFill]],
            ]);
        } else {
            $sheet->getStyle("F$r")->applyFromArray([
                'font' => ['color' => ['rgb' => $greenFill]],
            ]);
        }

        $sheet->getStyle("A$r:J$r")->applyFromArray([
            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgRow]],
            'font'    => ['color' => ['rgb' => $fgRow]],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '2A2520']]],
        ]);
        $sheet->getStyle("F$r")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A$r")->getAlignment()->setHorizontal('center');
    }

    // Filtros automáticos
    $sheet->setAutoFilter('A4:J4');

    // Hoja de resumen
    $sumSheet = $spread->createSheet()->setTitle('Resumen');
    $sumSheet->setCellValue('A1', 'Resumen del Inventario');
    $sumSheet->setCellValue('A3', 'Total insumos');
    $sumSheet->setCellValue('B3', count($rows));
    $sumSheet->setCellValue('A4', 'Agotados');
    $sumSheet->setCellValue('B4', count(array_filter($rows, fn($r) => $r['estado'] === 'AGOTADO')));
    $sumSheet->setCellValue('A5', 'Bajo Stock');
    $sumSheet->setCellValue('B5', count(array_filter($rows, fn($r) => $r['estado'] === 'BAJO STOCK')));
    $sumSheet->setCellValue('A6', 'Valor Total Inventario (COP)');
    $sumSheet->setCellValue('B6', array_sum(array_column($rows, 'valor_total')));
    $sumSheet->getStyle('B6')->getNumberFormat()->setFormatCode('#,##0.00');

    $spread->setActiveSheetIndex(0);

    // Output
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spread);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="inventario_' . date('Ymd_Hi') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}

// ── CSV Fallback (Exportación súper básica) ─────────────────────────────────────────
// Si no tenemos la librería PhpSpreadsheet, usamos las funciones nativas de PHP para crear un archivo separado por comas (.csv)
function exportCsv(array $rows): void {
    // Estas cabeceras le dicen al navegador que esto es un archivo descargable, no una página web
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inventario_' . date('Ymd_Hi') . '.csv"');
    
    // Abrimos el flujo de salida de PHP como si fuera un archivo
    $out = fopen('php://output', 'w');
    // BOM para que Excel lea los tildes y caracteres especiales en español correctamente
    fputs($out, "\xEF\xBB\xBF");
    
    // Imprimimos la primera fila (los títulos de las columnas)
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
