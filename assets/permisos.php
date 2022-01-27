<?php
function permiso($rol, $archivo)
{
    require "db.php";
    $sql = $db->prepare("SELECT `idPermisos` FROM `permisos` WHERE `RolesId` = ? AND `Archivo` = ?") or die($db->error);
    $sql->bind_param("is", $rol, $archivo);
    $sql->execute();
    $sql->store_result();
    $sql->bind_result($id);
    $sql->fetch();
    if ($sql->num_rows >= 1) {
        return true;
    } else {
        return false;
    }
}
