<?php
// =========================================================================
// SCRIPT DE REGISTRO
// =========================================================================
$conexion = new mysqli('localhost', 'root', '', 'restaurante_inventario');

if ($conexion->connect_errno) {
    die("ERROR al conectar con la DB: " . $conexion->connect_error);
}

if (isset($_POST['registrar'])) {
    
    // Recibimos los datos del formulario y prevenimos inyección SQL
    $su = $conexion->real_escape_string($_POST['usuario']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $clave_plana = $_POST['clave'];
    $pregunta = $conexion->real_escape_string($_POST['pregunta']);
    $respuesta = $conexion->real_escape_string($_POST['respuesta']);
    
    // Encriptamos la clave ingresada
    $c = md5($clave_plana);

    if (empty($su) || empty($clave_plana) || empty($correo)) {
        echo "<script>alert('Error: Todos los campos son obligatorios');</script>";
    } else {
        // Verificamos si el usuario ya existe para no duplicarlo
        $sql_check = "SELECT * FROM usuarios WHERE usunombre = '$su'";
        $resultado = $conexion->query($sql_check);

        if (mysqli_num_rows($resultado) > 0) {
            echo "<script>alert('Error: El nombre de usuario ya está en uso. Elige otro.');</script>";
        } else {
            // Inserción del nuevo usuario en la base de datos
            $sql_insert = "INSERT INTO usuarios (usunombre, usuclave, correo, pregunta_seguridad, respuesta_seguridad) 
                           VALUES ('$su', '$c', '$correo', '$pregunta', '$respuesta')";
            
            if ($conexion->query($sql_insert)) {
                echo "<script>alert('Usuario creado exitosamente. Ya puedes iniciar sesión.'); window.location='index.php';</script>";
            } else {
                echo "ERROR al registrar: " . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
            <h2 class="text-center mb-4">Crear Nuevo Usuario</h2>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="mb-3">
                    <label class="form-label">Usuario:</label>
                    <input class="form-control" type="text" name="usuario" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Correo:</label>
                    <input class="form-control" type="email" name="correo" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Clave:</label>
                    <input class="form-control" type="password" name="clave" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pregunta de Seguridad:</label>
                    <select class="form-select" name="pregunta" required>
                        <option value="">Selecciona una pregunta...</option>
                        <option value="mascota">¿Cuál es el nombre de tu primera mascota?</option>
                        <option value="colegio">¿En qué colegio estudiaste la primaria?</option>
                        <option value="ciudad">¿En qué ciudad nació tu madre?</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Respuesta:</label>
                    <input class="form-control" type="text" name="respuesta" required>
                </div>
                
                <input class="btn btn-primary w-100 mb-3" type="submit" name="registrar" value="Registrarme">
            </form>
            <div class="text-center">
                <a href="index.php" class="text-decoration-none">Volver al login</a>
            </div>
        </div>
    </div>
</body>
</html>