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
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4 col-md-4 align-items-center">
    <h2>Restablecer Contraseña</h2>
    
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="w-100 mt-3">
        <label>Usuario:</label>
        <input class="form-control mb-2" type="text" name="usuario" required>

        <label>Correo:</label>
        <input class="form-control mb-2" type="email" name="correo" required>
        
        <label>Pregunta de Seguridad:</label>
        <select class="form-select mb-2" name="pregunta" required>
            <option value="">Selecciona tu pregunta...</option>
            <option value="mascota">¿Cuál es el nombre de tu primera mascota?</option>
            <option value="colegio">¿En qué colegio estudiaste la primaria?</option>
            <option value="ciudad">¿En qué ciudad nació tu madre?</option>
        </select>
        
        <label>Respuesta a la pregunta:</label>
        <input class="form-control mb-2" type="text" name="respuesta" required>

        <label>Escribe tu nueva clave:</label>
        <input class="form-control mb-4" type="password" name="nueva_clave" required>

        <input class="btn btn-primary w-100 mb-2" type="submit" name="recuperar" value="Cambiar Contraseña">
    </form>
    <div class="text-center w-100 mt-2">
        <a href="index.php">Volver al login</a>
    </div>
    </div>
</body>
</html>