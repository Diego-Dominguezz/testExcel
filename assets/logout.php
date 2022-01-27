<?php
	session_start();
	setcookie("auth_r", null, 1, "/");
	unset($_COOKIE['auth_r']);
	session_destroy();
	header("Location: ../index.php");
?>