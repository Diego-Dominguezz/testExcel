<?php
if (isset($_POST['correo']) && isset($_POST['pass']) && isset($_POST['remember'])) {
    require_once "db.php";

    $sql = $db->prepare("SELECT `idUsuarios`, `usuarios`.`Nombre`, `ClienteId`, `Correo`, `Password`, `UltimoLogIn`, `RecordarSesion`, `RolId`,`roles`.`Nombre`, `usuarios`.`ingles` FROM `usuarios`
       INNER JOIN `roles`  on `usuarios`.`RolId` = `roles`.`idRoles`
       WHERE `Correo` = ?") or die($db->error);
    if ($sql) {
        $sql->bind_param("s", $_POST['correo']);
        $sql->execute();
        $sql->store_result();
        $sql->bind_result($id, $usuario, $clienteid, $correo, $pass, $lastlogin, $codigoseguridad, $rol,$nombreRol, $ingles);
        $sql->fetch();
        if ($sql->num_rows > 0) {
            if (password_verify($_POST['pass'], $pass)) {
                if ($_POST['remember'] == 1) {
                    $codigoSeguridad = md5(uniqid(rand(), true));
                    $hoy = time();
                    $stmt = $db->prepare("UPDATE `usuarios` SET `RecordarSesion` = ?, `UltimoLogIn` = ? WHERE `idUsuarios` = ?") or die($db->error);
                    if ($stmt) {
                        $stmt->bind_param('sii', $codigoSeguridad, $hoy, $id);
                        $stmt->execute();
                    }
                    setcookie("remember", null, 1, "/");
                    unset($_COOKIE['remember']);
                    setcookie("remember", $codigoSeguridad, time()+60*60*24*30, "/");
                    session_start();
                    unset($_SESSION['logged']);
                    $_SESSION['logged'] = $id;
                    $_SESSION['usuario'] = $usuario;
                    $_SESSION['cliente'] = $clienteid;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['rol'] = $rol;
                    $_SESSION['nombre_rol'] = $nombreRol;
                    $_SESSION['ingles'] = $ingles;
                    echo 0;
                    // if ($rol == 4) {
                    //     echo 2;
                    // } else if ($rol == 5) {
                    //     echo 3;
                    // } else
                    //     echo 0;
                } else {
                    $hoy = time();
                    $stmt = $db->prepare("UPDATE `usuarios` SET `UltimoLogIn` = ? WHERE `idUsuarios` = ?") or die($db->error);
                    if ($stmt) {
                        $stmt->bind_param('si', $hoy, $id);
                        $stmt->execute();
                    }
                    session_start();
                    unset($_SESSION['logged']);
                    $_SESSION['logged'] = $id;
                    $_SESSION['usuario'] = $usuario;
                    $_SESSION['cliente'] = $clienteid;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['rol'] = $rol;
                    $_SESSION['nombre_rol'] = $nombreRol;
                    $_SESSION['ingles'] = $ingles;
                    echo 0;
                    // if ($rol == 4) {
                    //     echo 2;
                    // } else if ($rol == 5) {
                    //     echo 3;
                    // } else
                    //     echo 0;
                }
            } else {
                echo 1; // USUARIO O CONTRASE:A INCORRECTOS
            }
        } else {
            echo 1; // USUARIO O CONTRASE:A INCORRECTOS
        }
        $sql->close();
    } else {
        echo 1;
    }
    $db->close();
} else {
    echo "isset";
}
