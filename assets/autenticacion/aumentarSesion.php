<?php
    session_start();
    require("../db.php");
    require("../../clases/autenticacion/auth.class.php");
    $auth = new Auth($db);
    echo $auth->aumentarSesion();
