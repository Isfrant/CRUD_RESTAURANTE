<?php
// =========================================================================
// SCRIPT DE LOGIN
// =========================================================================
// Iniciamos la sesión para poder guardar las variables globales del usuario
session_start();

// Conexión a la base de datos del restaurante
$conexion = new mysqli('localhost', 'root', '', 'restaurante_inventario');

if ($conexion->connect_errno) {
    die("ERROR al conectar con la DB: " . $conexion->connect_error);
}

// Verificamos si se envió el formulario (el botón se llama 'login')
if (isset($_POST['login'])) {
    
    // Obtenemos los datos del formulario y los limpiamos para evitar inyección SQL
    $su = $conexion->real_escape_string($_POST['usuario']);
    $clave_plana = $_POST['clave'];
    
    // Encriptamos la clave con md5 tal como en tu código original
    $c = md5($clave_plana);  

    // Validamos que no estén vacíos
    if (empty($su) || empty($clave_plana)) {
        echo "<script>alert('Error: usuario y/o clave vacíos!!');</script>"; 
    } else {
        // Hacemos la consulta a la tabla usuarios (usamos usunombre y usuclave)
        $sql = "SELECT * FROM usuarios WHERE usunombre = '$su' AND usuclave = '$c'";
        
        $consulta = $conexion->query($sql);

        if (!$consulta) {
            echo "ERROR: no se pudo ejecutar la consulta!";
        } else {
            // Contamos cuántos registros coincidieron
            $filas = mysqli_num_rows($consulta);

            // Si es 0, los datos están mal
            if ($filas == 0) {
                echo "<script>alert('Error: usuario y/o clave incorrectos!!');</script>";
            } else {
                // Si la consulta fue exitosa, extraemos los datos del usuario
                $usuario_db = $consulta->fetch_assoc();
                
                // GUARDAMOS LOS DATOS EN LA SESIÓN (Muy importante para proteger el CRUD)
                $_SESSION['usuario_id'] = $usuario_db['id'];
                $_SESSION['usuario_nombre'] = $usuario_db['usunombre'];
                
                // Redirigimos al panel principal del CRUD (Vue)
                echo "<script> window.location='home.php';</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login del Proyecto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Iniciar Sesión</h2>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="mb-3">
                    <label class="form-label">Usuario:</label>
                    <input class="form-control" type="text" name="usuario" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Clave:</label>
                    <input class="form-control" type="password" name="clave" required>
                </div>
                
                <input class="btn btn-primary w-100 mb-3" type="submit" name="login" value="Ingresar"> 
            </form>
            
            <div class="text-center">
                <a href="recuperar.php" class="d-block mb-2 text-decoration-none">¿Olvidaste tu contraseña?</a>
                <a href="registro.php" class="d-block text-decoration-none">Crear una cuenta nueva</a>
            </div>
        </div>
    </div>
</body>
</html>