<?php
if (isset($_POST['userid'])) {
	require_once "db.php";

	$query = $db->query("DELETE FROM `usuarios` WHERE `idUsuarios` = ".$_POST['userid']);
	echo "0";
}

?>