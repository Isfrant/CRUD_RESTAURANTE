<?php
// ============================================================
// Exportar PDF – Alertas de Stock Mínimo
// Requiere: composer require setasign/fpdf   O  descargar fpdf.php
// ============================================================
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php'); exit;
}

require_once __DIR__ . '/../config/conexion.php';

// ── Intentar cargar FPDF ──────────────────────────────────
$fpdfPaths = [
    __DIR__ . '/../vendor/setasign/fpdf/fpdf.php',    // composer
    __DIR__ . '/../lib/fpdf/fpdf.php',                 // manual
    __DIR__ . '/fpdf.php',                             // misma carpeta
];
$loaded = false;
foreach ($fpdfPaths as $p) {
    if (file_exists($p)) { require_once $p; $loaded = true; break; }
}

if (!$loaded) {
    // Fallback: descarga inline sin FPDF (HTML→PDF básico vía cabeceras)
    fallbackHtml();
    exit;
}

// ── Datos ─────────────────────────────────────────────────
$pdo  = getConexion();
// Ejecutamos una consulta SQL (SELECT) para traer los insumos que tengan un stock_actual menor al stock_minimo (Alertas)
$stmt = $pdo->query(
    'SELECT i.nombre, i.stock_actual, i.stock_minimo, i.precio_unitario,
            i.fecha_vencimiento, c.nombre AS categoria
     FROM insumos i
     LEFT JOIN categorias c ON i.categoria_id = c.id
     WHERE i.stock_actual < i.stock_minimo
     ORDER BY (i.stock_actual / NULLIF(i.stock_minimo,0)) ASC, i.nombre'
);
$items = $stmt->fetchAll();

// ── PDF con FPDF ──────────────────────────────────────────
class PDF extends FPDF {
    function Header() {
        // Fondo header
        $this->SetFillColor(15, 13, 11);
        $this->Rect(0, 0, 210, 30, 'F');
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(201, 168, 76);
        $this->SetY(8);
        $this->Cell(0, 8, 'Restaurante', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(180, 170, 155);
        $this->Cell(0, 5, 'Reporte de Alertas de Stock Minimo', 0, 1, 'C');
        $this->Ln(4);
    }
    function Footer() {
        $this->SetY(-14);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(150);
        $this->Cell(0, 6, 'Generado el ' . date('d/m/Y H:i') . '  |  Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// Metadata
$fecha = date('d/m/Y H:i');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100);
$pdf->Cell(0, 6, 'Fecha de generacion: ' . $fecha . '   |   Total alertas: ' . count($items), 0, 1);
$pdf->Ln(3);

if (count($items) === 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(39, 174, 96);
    $pdf->Cell(0, 20, '¡Excelente! Todos los insumos tienen stock suficiente.', 0, 1, 'C');
} else {
    // Encabezados tabla
    $headers = ['Insumo', 'Categoria', 'Stock Actual', 'Stock Min.', 'Deficit', 'Precio Unit.', 'Vencimiento'];
    $widths  = [60, 40, 28, 28, 28, 32, 32];

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(30, 26, 22);
    $pdf->SetTextColor(201, 168, 76);
    $pdf->SetDrawColor(60, 50, 30);
    $pdf->SetLineWidth(0.3);
    foreach ($headers as $k => $h) {
        $pdf->Cell($widths[$k], 9, $h, 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8);
    $fill = false;
    foreach ($items as $row) {
        $deficit = floatval($row['stock_minimo']) - floatval($row['stock_actual']);
        $esAgot  = floatval($row['stock_actual']) == 0;

        if ($esAgot) {
            $pdf->SetFillColor(80, 20, 15);
            $pdf->SetTextColor(220, 100, 90);
        } else {
            $pdf->SetFillColor($fill ? 28 : 22, $fill ? 24 : 18, $fill ? 18 : 14);
            $pdf->SetTextColor(200, 190, 175);
        }

        $precio = '$' . number_format($row['precio_unitario'], 0, ',', '.');
        $venc   = $row['fecha_vencimiento']
                    ? date('d/m/Y', strtotime($row['fecha_vencimiento']))
                    : '—';

        $pdf->Cell($widths[0], 8, iconv('UTF-8', 'windows-1252//TRANSLIT', $row['nombre']),    1, 0, 'L', true);
        $pdf->Cell($widths[1], 8, iconv('UTF-8', 'windows-1252//TRANSLIT', $row['categoria']), 1, 0, 'C', true);
        $pdf->Cell($widths[2], 8, number_format($row['stock_actual'], 2), 1, 0, 'C', true);
        $pdf->Cell($widths[3], 8, number_format($row['stock_minimo'], 2), 1, 0, 'C', true);
        $pdf->Cell($widths[4], 8, number_format($deficit, 2),             1, 0, 'C', true);
        $pdf->Cell($widths[5], 8, $precio,                                1, 0, 'C', true);
        $pdf->Cell($widths[6], 8, $venc,                                  1, 1, 'C', true);
        $fill = !$fill;
    }

    // Resumen
    $pdf->Ln(6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(201, 168, 76);
    $pdf->SetFillColor(30, 26, 22);
    $agotados = array_filter($items, fn($i) => floatval($i['stock_actual']) == 0);
    $pdf->Cell(0, 8, 'Resumen: ' . count($items) . ' alerta(s) | ' . count($agotados) . ' completamente agotado(s)', 0, 1, 'L', true);
}

$pdf->Output('D', 'alertas_stock_' . date('Ymd_Hi') . '.pdf');

