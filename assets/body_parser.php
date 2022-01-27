<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)){
        $_POST = json_decode(file_get_contents('php://input'), true);
        $_REQUEST = json_decode(file_get_contents('php://input'), true);
    }