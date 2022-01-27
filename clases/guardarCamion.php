<?php

	if (
		isset($_POST['serie']) && 
		isset($_POST['modelo']) && 
		isset($_POST['marca']) && 
		isset($_POST['placas']) && 
		isset($_POST['no_camion']) && 
		isset($_POST['millas']) && 
		isset($_POST['id_cliente'])
	){

		require("../assets/db.php");

		$serie = $_POST['serie'];
		$modelo = $_POST['modelo'];
		$marca = $_POST['marca'];
		$placas = $_POST['placas'];
		$no_camion = $_POST['no_camion'];
		$millas = $_POST['millas'];
		$id_cliente = $_POST['id_cliente'];

		$myvalue = $serie . $modelo . $marca . $placas . $no_camion . $millas . $id_cliente;

		$stmt = $db->prepare("
			INSERT INTO  camiones (serie, modelo, marca, placa, millas, numeroCamion, clienteId) 
			values(?,?,?,?,?,?,?);") or die ('prepare() error:' . htmlspecialchars(mysqli_error($db)));

		$stmt->bind_param(
			"ssssisi",
			$serie,
			$modelo,
			$marca,
			$placas,
			$millas,
			$no_camion,
			$id_cliente
		);

		// echo $stmt;

		if(!$stmt->execute()){
			$response['message'] = 'Error ejecutando el query';
			$response['success'] = false;
			$response['sql'] = 'execute() failed: ' . htmlspecialchars($stmt->error);
		}else{
			$response['message'] = 'Guardado exitoso';
			$response['success'] = true;

		}
		$stmt->close();

		
		echo json_encode($response);
	}else{
		$response['message'] = 'Informacin invalida';
		$response['success'] = false;

		echo json_encode($response);
	}

?>