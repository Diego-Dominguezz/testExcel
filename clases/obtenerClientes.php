<?php
	if ($_POST['']) {
		# code...
	}
	require("../assets/db.php");

	$q = $db->prepare("SELECT idClientes, Nombre, prefijoDriver FROM clientes WHERE Status = 1") or trigger_error($db->error);
	$q->execute();
	if (!$q->error) {
		# Aqui es para manejar el error pero como no tengo idea de como imprimirlo pues me vale vrg
		// echo json_encode($q->error); 
	}
	// $q->bind_result($idClientes, $nombre, $prefijoDriver);

	// $res = $q->get_result();
	$res = $q->fetchAll();

	$q->close();
	// $res = $res->fetch_all();
	
	// $res = $res->fullQuery();

	$result = array();
	foreach ($res as $row) {
		$result[] = $row;
	}

	// echo '["' . implode('","', $res) . '""]';
	// echo $res;

	print_r(json_encode($result));

?>