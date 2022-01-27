<?php
session_start();
if (isset($_REQUEST['userid'])) {

    require_once "db.php";

   $upermisos = $db->query("SELECT * FROM `usuarios` WHERE `idUsuarios` = ".$_REQUEST['userid']) or die($db->error);
    if ($upermisos) {
        while ($row = $upermisos->fetch_assoc()) {
            $rol = $row['RolId'];
        }
    }
    $permisos = $db->query("SELECT * FROM `roles`") or die($db->error);
    if ($permisos) {
    	while ($row = $permisos->fetch_assoc()) {
            $permisosf[] = [
                'id' => $row['idRoles'],
                'rol' => $row['Nombre'],
                'status' => ($rol == $row['idRoles']) ? "1" : "0"
            ];
    	}
        $array = $permisosf;
    }
    
    
    if (isset($array)) {
        $json = json_encode($array);
        echo $json;
    } else {
        echo 0;
    }
    $db->close();
}
?>