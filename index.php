<?php

require('./excel/php-excel-reader/excel_reader2.php');
require('./excel/SpreadsheetReader.php');
require("./assets/db.php");
require("./assets/conf.php");
require('./logs/logs.php');

melher();

function melher()
{
  global $stmt, $db;
  global $reciboDriverId, $reciboMillas, $reciboTarifa, $reciboTipoPago, $reciboFecha, $reciboInicioViaje, $reciboFinalViaje, $reciboIdEmpresa, $reciboTipoTarifa;

  $archivo = "./GR EXPRESS P 03.xlsx";

  //move_uploaded_file($_FILES['payroll']['tmp_name'], $archivo);


  $Spreadsheet = new SpreadsheetReader($archivo);



  $Sheets = $Spreadsheet->Sheets();


  // foreach ($Sheets as $Index => $Name){
  // $Spreadsheet -> ChangeSheet(1);

  $conductor = new Conductor();

  foreach ($Spreadsheet as $key => $row) { // Inicia por row



    if ($key == 0) {
      continue;
    }

    if ($conductor->driverId != $row[0]) {

      foreach ($conductor->viajes as $key => $viaje) {
        $reciboDriverId = $conductor->driverId;
        $reciboMillas = $viaje->millas;
        $reciboTarifa = $viaje->rate;
        $reciboTipoPago = $viaje->tipoPago;
        $reciboInicioViaje = $viaje->origen;
        $reciboFinalViaje = $viaje->destino;
        //$reciboIdEmpresa = $_SESSION['cliente'];
        $reciboTipoTarifa = $viaje->tipoTarifa;
        $reciboFecha = strtotime("last sunday");
        //$stmt->execute() or die(mysqli_error($db) . "error 2");
      }

      $conductor = new Conductor();
      $conductor->driverId = trim($row[0]); // trim driverid
      if (!$row[2]) return;
    }

    $conceptoGeneral = "OTHER";


    if ($row[9] != "" && $row[9] > 0) {
      $conductor->viajes[] = new Viaje("", "", 1, $row[9], $conceptoGeneral, 3); // bono 
    }
    if ($row[11] != "" && $row[11] > 0) {
      $conductor->viajes[] = new Viaje("", "", abs($row[11]), 1, $conceptoGeneral, 2); // deduccion
    }
    if ($row[3]) {
      $conductor->viajes[] = new Viaje("", "", $row[3], $row[4], "", 0);
    }
    echo json_encode($conductor);
    echo $key;
    echo "------";
    // if ($key > 5) {
    //   return;
    // }
  } // Termina por row


}
class Conductor
{
  public $driverId, $viajes = array(), $viajesCapturados = false, $rowDeducciones = -1, $falta = false;
  public $rates;


  public function getRobo()
  {
    global $db;
    $res = mysqli_query($db, "SELECT * FROM conductores WHERE driverId = '" . $this->driverId . "' AND idCompania = '" . $_SESSION['cliente'] . "'") or die(mysqli_error($db));
    $row = mysqli_fetch_assoc($res);
    $this->rates = new Rates($row['MillasTeam'], $row['MillasAlone']);
  }
}

class Viaje
{
  public $millas, $origen, $destino, $rate, $tipoPago, $tipoTarifa, $inicioViaje, $finalViaje;

  function __construct($origen, $destino, $millas, $rate, $tipoPago, $tipoTarifa)
  {
    $this->origen = $origen;
    $this->destino = $destino;
    $this->millas = $millas;
    $this->rate = $rate;
    $this->tipoPago = $tipoPago;
    $this->tipoTarifa = $tipoTarifa;
  }
}
