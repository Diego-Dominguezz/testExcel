<?php
if (isset($_POST)){
require("../assets/db.php");


	$stmt = $db->prepare("UPDATE conductores SET conductores.GafeteAduana = ? WHERE conductores.idConductores = ?") or die (mysqli_error($db));

	$stmt->bind_param(
		"ii",
		$_POST['value'],
		$_POST['id']
	);

	// echo $stmt;

	$stmt->execute();
	$stmt->close();

	// $response['sql'] = $stmt;
	// $response['message'] = "funciona!";
	$response['message'] = 'success';
	// $response['value'] = $_POST['value'];ß
	echo json_encode($response);
}

// $str = new AsignarGafete();
// $str->asignarGafete2($_POST['id'], $_POST['value'], $db);

?>
