<?php
	if($_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "transnortetest"){
		$db = mysqli_connect("localhost", "root", "", "transnortetest");
		// $db = mysqli_connect("keyinnovation.org", "innovati_admin", "&9b63h4U#", "innovati_transnorte2")or die("Sin conexión");
	}else{
		$db = mysqli_connect("localhost", "innovati_admin", "&9b63h4U#", "innovati_transnorte2")or die("Sin conexión");
		require(__DIR__.'/../logs/logrequest.php');
	}

 ?>
