<?php
// ============================================================
// API REST – Gestión de Insumos
// ============================================================
// Iniciamos la sesión para proteger la API (solo usuarios logueados pueden hacer CRUD)
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    // Devolvemos un JSON indicando error de autorización si intentan acceder directamente
    echo json_encode(['ok' => false, 'error' => 'No autorizado.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexion.php';

$pdo    = getConexion();
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// ── Enrutador (Controlador Frontal) ─────────────────────────────────────────────
// Dependiendo de lo que Vue.js envíe en 'action', ejecutamos una función diferente
switch ($action) {
    case 'list':        getList($pdo);       break; // Obtiene los insumos (READ)
    case 'categorias':  getCategorias($pdo); break; // Obtiene las categorías
    case 'create':      createInsumo($pdo);  break; // Guarda un insumo nuevo (CREATE)
    case 'update':      updateInsumo($pdo);  break; // Actualiza un insumo (UPDATE)
    case 'delete':      deleteInsumo($pdo);  break; // Elimina un insumo (DELETE)
    default:
        echo json_encode(['ok' => false, 'error' => 'Acción no válida.']);
}

// ── GET: lista de insumos (READ) ─────────────────────────────────
function getList(PDO $pdo): void {
    // Hacemos un JOIN con categorías para traer el nombre de la categoría, no solo su ID
    $stmt = $pdo->query(
        'SELECT i.*, c.nombre AS categoria_nombre
         FROM insumos i
         LEFT JOIN categorias c ON i.categoria_id = c.id
         ORDER BY i.nombre'
    );
    // Devolvemos el resultado como un JSON para que Vue pueda leerlo
    echo json_encode(['ok' => true, 'data' => $stmt->fetchAll()]);
}

// ── GET: lista de categorías ───────────────────────────────
function getCategorias(PDO $pdo): void {
    $stmt = $pdo->query('SELECT id, nombre FROM categorias ORDER BY nombre');
    echo json_encode(['ok' => true, 'data' => $stmt->fetchAll()]);
}

// ── POST: crear insumo (CREATE) ────────────────────────────────────
function createInsumo(PDO $pdo): void {
    // 1. Validar que los campos obligatorios vengan en el POST
    $data = validarCampos();
    if ($data['error']) { echo json_encode(['ok' => false, 'error' => $data['error']]); return; }

    // 2. Procesar la subida de imagen (si la hay)
    $imgRuta = subirImagen();
    if ($imgRuta['error']) { echo json_encode(['ok' => false, 'error' => $imgRuta['error']]); return; }

    // 3. Preparar la consulta SQL (Evitando inyección SQL con parámetros :nombre, :stock_actual...)
    $stmt = $pdo->prepare(
        'INSERT INTO insumos (nombre, stock_actual, stock_minimo, precio_unitario, fecha_vencimiento, categoria_id, imagen_ruta)
         VALUES (:nombre, :stock_actual, :stock_minimo, :precio_unitario, :fecha_vencimiento, :categoria_id, :imagen_ruta)'
    );
    
    // 4. Ejecutar la inserción con los datos
    $stmt->execute([
        ':nombre'            => $data['nombre'],
        ':stock_actual'      => $data['stock_actual'],
        ':stock_minimo'      => $data['stock_minimo'],
        ':precio_unitario'   => $data['precio_unitario'],
        ':fecha_vencimiento' => $data['fecha_vencimiento'],
        ':categoria_id'      => $data['categoria_id'],
        ':imagen_ruta'       => $imgRuta['ruta'],
    ]);
    
    // Respondemos con ok: true y el ID generado
    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
}

// ── POST: actualizar insumo ───────────────────────────────
function updateInsumo(PDO $pdo): void {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID no válido.']); return; }

    $data = validarCampos();
    if ($data['error']) { echo json_encode(['ok' => false, 'error' => $data['error']]); return; }

    // Obtener imagen actual
    $actual = $pdo->prepare('SELECT imagen_ruta FROM insumos WHERE id = ?');
    $actual->execute([$id]);
    $row = $actual->fetch();

    $imgRuta = subirImagen();
    if ($imgRuta['error']) { echo json_encode(['ok' => false, 'error' => $imgRuta['error']]); return; }

    // Si se subió nueva imagen, borrar la antigua
    $nuevaRuta = $imgRuta['ruta'];
    if ($nuevaRuta && $row && $row['imagen_ruta']) {
        $antigua = __DIR__ . '/../' . $row['imagen_ruta'];
        if (file_exists($antigua)) @unlink($antigua);
    }
    $rutaFinal = $nuevaRuta ?: ($row['imagen_ruta'] ?? null);

    $stmt = $pdo->prepare(
        'UPDATE insumos SET nombre=:nombre, stock_actual=:stock_actual, stock_minimo=:stock_minimo,
         precio_unitario=:precio_unitario, fecha_vencimiento=:fecha_vencimiento,
         categoria_id=:categoria_id, imagen_ruta=:imagen_ruta
         WHERE id=:id'
    );
    $stmt->execute([
        ':nombre'            => $data['nombre'],
        ':stock_actual'      => $data['stock_actual'],
        ':stock_minimo'      => $data['stock_minimo'],
        ':precio_unitario'   => $data['precio_unitario'],
        ':fecha_vencimiento' => $data['fecha_vencimiento'],
        ':categoria_id'      => $data['categoria_id'],
        ':imagen_ruta'       => $rutaFinal,
        ':id'                => $id,
    ]);
    echo json_encode(['ok' => true]);
}

// ── POST: eliminar insumo ─────────────────────────────────
function deleteInsumo(PDO $pdo): void {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID no válido.']); return; }

    $stmt = $pdo->prepare('SELECT imagen_ruta FROM insumos WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['imagen_ruta']) {
        $ruta = __DIR__ . '/../' . $row['imagen_ruta'];
        if (file_exists($ruta)) @unlink($ruta);
    }
    $del = $pdo->prepare('DELETE FROM insumos WHERE id = ?');
    $del->execute([$id]);
    echo json_encode(['ok' => true]);
}

// ── Helpers ───────────────────────────────────────────────
function validarCampos(): array {
    $nombre          = trim($_POST['nombre']           ?? '');
    $stock_actual    = $_POST['stock_actual']    ?? '';
    $stock_minimo    = $_POST['stock_minimo']    ?? '';
    $precio_unitario = $_POST['precio_unitario'] ?? '';
    $fecha           = trim($_POST['fecha_vencimiento'] ?? '');
    $categoria_id    = intval($_POST['categoria_id']   ?? 0);

    if (!$nombre)          return ['error' => 'El nombre es obligatorio.'];
    if (!$categoria_id)    return ['error' => 'La categoría es obligatoria.'];
    if ($stock_actual === '') return ['error' => 'El stock actual es obligatorio.'];
    if ($stock_minimo === '') return ['error' => 'El stock mínimo es obligatorio.'];
    if ($precio_unitario === '') return ['error' => 'El precio unitario es obligatorio.'];

    return [
        'error'            => '',
        'nombre'           => $nombre,
        'stock_actual'     => floatval($stock_actual),
        'stock_minimo'     => floatval($stock_minimo),
        'precio_unitario'  => floatval($precio_unitario),
        'fecha_vencimiento'=> $fecha ?: null,
        'categoria_id'     => $categoria_id,
    ];
}

function subirImagen(): array {
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ruta' => null, 'error' => ''];
    }
    $file = $_FILES['imagen'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ruta' => null, 'error' => 'Error al subir la imagen (código ' . $file['error'] . ').'];
    }
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['ruta' => null, 'error' => 'La imagen supera el límite de 2 MB.'];
    }
    $mime     = mime_content_type($file['tmp_name']);
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowed, true)) {
        return ['ruta' => null, 'error' => 'Tipo de imagen no permitido. Usa JPG, PNG o WEBP.'];
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre   = uniqid('img_', true) . '.' . strtolower($ext);
    $destino  = UPLOAD_DIR . $nombre;
    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        return ['ruta' => null, 'error' => 'No se pudo mover el archivo. Verifica permisos de la carpeta uploads/.'];
    }
    return ['ruta' => UPLOAD_URL . $nombre, 'error' => ''];
}
