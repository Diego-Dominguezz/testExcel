<?php
// return;
  session_start();
  set_time_limit(0);
  require("../assets/db.php");
  require("../assets/conf.php");
  require("conexion.php");
  require("driver.php");
  require("recibo.php");
  require("viaje.php");
  require("driverMesilla.php");
  require('../excel/php-excel-reader/excel_reader2.php');
  require('../excel/SpreadsheetReader.php');
  require("../logs/logs.php");
  require("../notificaciones/notificaciones_main.php");

  $_conf = new Configuracion();

  if(!isset($_GET['empresa'])){
    return;
  }

  if(!isset($_SESSION['rol']) && $_SESSION['rol'] != '1' && $_SESSION['rol'] != '2' && $_SESSION['rol'] != '8'){ // No está autorizado para hacer nómina
    header("Location: ../index.php");
    return;
  }

  $compania = "";
  $stmt = $db->prepare("SELECT Nombre FROM clientes WHERE idClientes = ?")or die(mysqli_error($db));
  $stmt->bind_param("i", $_GET['empresa']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($compania);
  $stmt->fetch();

  $sql = mysqli_query($db, "SELECT DISTINCT driverid FROM nom INNER JOIN clientes ON nom.idEmpresa = clientes.idClientes WHERE clientes.Nombre = '$compania' AND semana = '".$_conf->semana."' AND ano = '".$_conf->ano."' GROUP BY driverid")or die(mysqli_error($db));
  $conductoresQueCorrieron = [];
  try{
    while ($row = mysqli_fetch_assoc($sql)) {
      try{
      	if(!isset($row['driverid']) || $row['driverid'] == ''){
      		continue;
      	}
        $conductoresQueCorrieron[] = $row['driverid'];
        $driver = new Driver($row['driverid']);
        $recibo = new Recibo($driver);
      }catch(Exception $err){
        echo $err->getMessage();
        return;
      }
    }
  }catch(Exception $e){
    echo $e->getMessage();
  }

  $todosLosConductoresQueExisten = [];
  $resConductores = mysqli_query($db, "SELECT conductores.* FROM conductores INNER JOIN recibos ON conductores.DriverId = recibos.driverid WHERE conductores.Compania = '$compania' AND conductores.Status <> 0 GROUP BY conductores.DriverId"); // La variable ya esta limpia

  while ($rowConductores = mysqli_fetch_assoc($resConductores)) {
    $todosLosConductoresQueExisten[] = $rowConductores['DriverId'];
  }

// Inicia el proceso de inserción de faltas

  $stmt = $db->prepare("INSERT INTO faltas (driverId, semana, ano, idEmpresa) VALUES (?, ?, ?, ?)")or die(mysqli_error("ERROR".$db));
  $stmt->bind_param(
    "ssii",
    $driverIdFaltante,
    $_conf->semana,
    $_conf->ano,
    $_GET['empresa']
  );

  $conductoresQueFaltaron = [];

  foreach ($todosLosConductoresQueExisten as $key => $value) {
    if(!in_array($value, $conductoresQueCorrieron)){
      $conductoresQueFaltaron[] = $value;
    }
  }

  foreach ($conductoresQueFaltaron as $key => $value) {
    $driverIdFaltante = $value;

    $resPermiso = mysqli_query($db, "SELECT * FROM conductores WHERE DriverId = '$driverIdFaltante'");
    $rowPermiso = mysqli_fetch_assoc($resPermiso);

    $sqlContadorFaltas = "SELECT * FROM faltas WHERE semana = '".$_conf->semana."' AND ano = '".$_conf->ano."' AND driverId = '$driverIdFaltante'";
    $contadorFaltas = mysqli_query($db, $sqlContadorFaltas)or die(mysqli_error($db));
    if( mysqli_num_rows( $contadorFaltas ) == 0 ){ // Ingresa una falta nueva si no existe la falta de esta semana
      $resContadorFaltas = mysqli_query($db, "SELECT * FROM faltas WHERE driverId = '$driverIdFaltante' AND semanaCobrado IS NULL AND anoCobrado IS NULL")or die(mysqli_error($db));
      if(mysqli_num_rows( $resContadorFaltas ) >= 2){ // Confirmo, está bien la condición. La tercera falta no se agrega, además, si son 2 faltas, quiere decir que la última falta no es de la fecha actual.
        if($rowPermiso['Status'] == 3){ // Permiso personal
          continue;
        }
        mysqli_query($db, "UPDATE conductores SET Status = 0 WHERE DriverId = '$driverIdFaltante'")or die( mysqli_error($db) );
      }else{
        $permisoFalta = $db->query("SELECT conductores.DriverId, permisos_conductores.* FROM `permisos_conductores` INNER JOIN conductores ON conductores.idConductores = permisos_conductores.idConductor WHERE permisos_conductores.ano IS NULL AND permisos_conductores.semana IS NULL AND conductores.DriverId = '".$driverIdFaltante."'")or die(mysqli_error($db));
        $rowPermiso = $permisoFalta->fetch_assoc();
        if($rowPermiso){
          $db->query("UPDATE permisos_conductores SET semana = '".$_conf->semana."', ano = '".$_conf->ano."' WHERE id = ".$rowPermiso['id'])or die(mysqli_error($db));
        }else{
          $stmt->execute();
        }
      }
    }
  }



  // Termina inserción de faltas

  $res = mysqli_query($db, "SELECT COUNT(*) as erroresCount FROM errores_nom WHERE idEmpresa = (SELECT idClientes FROM clientes WHERE Nombre = '$compania' LIMIT 1)");
  $row = mysqli_fetch_assoc($res);


  if($_SERVER['SERVER_NAME'] != "localhost"){ // Hacer nómina
    insertarLog(8, $compania);
  }
  Notificaciones::insertar($db, [1, 2, 8], $_SESSION['logged'], 3, json_encode(array("empresa" => $compania)));


  if($row['erroresCount'] > 0){
    echo ('Existen errores en nómina');
  }else{
    echo ('Nómina hecha con éxito');
  }
  // window.location.href = "../panelErroresNomina.php?idEmpresa=<?= htmlspecialchars($_GET['empresa'], ENT_QUOTES, 'UTF-8');
