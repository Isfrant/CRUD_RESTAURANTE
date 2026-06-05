<?php
// =========================================================================
// SCRIPT DE RECUPERACIÓN DE CONTRASEÑA
// =========================================================================
$conexion = new mysqli('localhost', 'root', '', 'restaurante_inventario');

if ($conexion->connect_errno) {
    die("ERROR al conectar con la DB: " . $conexion->connect_error);
}

if (isset($_POST['recuperar'])) {
    // Recibimos los datos de recuperación
    $su = $conexion->real_escape_string($_POST['usuario']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $pregunta = $conexion->real_escape_string($_POST['pregunta']);
    $respuesta = $conexion->real_escape_string($_POST['respuesta']);
    $nueva_clave_plana = $_POST['nueva_clave'];

    if (empty($su) || empty($correo) || empty($pregunta) || empty($respuesta) || empty($nueva_clave_plana)) {
        echo "<script>alert('Llena todos los campos');</script>";
    } else {
        // Buscamos si el usuario y los demás campos coinciden en la base de datos
        $sql = "SELECT * FROM usuarios WHERE usunombre = '$su' AND correo='$correo' AND pregunta_seguridad = '$pregunta' AND respuesta_seguridad = '$respuesta'";
        $consulta = $conexion->query($sql);

        if (mysqli_num_rows($consulta) > 0) {
            // Si todo coincide, encriptamos la nueva clave en md5
            $nueva_clave_md5 = md5($nueva_clave_plana); 
            
            // Actualizamos la clave en la tabla
            $sql_update = "UPDATE usuarios SET usuclave = '$nueva_clave_md5' WHERE usunombre = '$su'";
            
            if ($conexion->query($sql_update)) {
                echo "<script>alert('Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.'); window.location='index.php';</script>";
            } else {
                echo "ERROR al actualizar: " . $conexion->error;
            }
        } else {
            echo "<script>alert('Los datos de recuperación son incorrectos.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Clave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
            <h2 class="text-center mb-4">Recuperar Contraseña</h2>
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
                    <label class="form-label">Pregunta de Seguridad:</label>
                    <select class="form-select" name="pregunta" required>
                        <option value="">Selecciona una pregunta...</option>
                        <option value="mascota">¿Cuál es el nombre de tu primera mascota?</option>
                        <option value="colegio">¿En qué colegio estudiaste la primaria?</option>
                        <option value="ciudad">¿En qué ciudad nació tu madre?</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Respuesta:</label>
                    <input class="form-control" type="text" name="respuesta" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Nueva Contraseña:</label>
                    <input class="form-control" type="password" name="nueva_clave" required>
                </div>

                <input class="btn btn-primary w-100 mb-3" type="submit" name="recuperar" value="Cambiar Contraseña">
            </form>
            <div class="text-center">
                <a href="index.php" class="text-decoration-none">Volver al login</a>
            </div>
        </div>
    </div>
</body>
</html>