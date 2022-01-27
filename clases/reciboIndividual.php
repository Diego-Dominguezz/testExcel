<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<?php
  set_time_limit(0);
  require("../assets/db.php");
  require("conexion.php");
  require("driver.php");
  require("recibo.php");
  require("viaje.php");
  require("driverMesilla.php");
  require('../excel/php-excel-reader/excel_reader2.php');
  require('../excel/SpreadsheetReader.php');

  if(!isset($_GET['empresa'])){
    return;
  }

  $compania = "";
  $stmt = $db->prepare("SELECT Nombre FROM clientes WHERE idClientes = ?")or die(mysqli_error($db));
  $stmt->bind_param("i", $_GET['empresa']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($compania);
  $stmt->fetch();

  $file = file_get_contents("../assets/config.json");
  $json = json_decode($file, true);

  // $fecha_actual = date("N");
  //
  // if($fecha_actual == $json['diaNum'])
  //   $ultimoViernes = strtotime("today");
  // else
    $ultimoViernes = strtotime("last ".$json['dia']);


  $driverId = mysqli_real_escape_string($db, $_GET['driverid']);
  $sql = mysqli_query($db, "SELECT DriverId FROM conductores WHERE idConductores = '$driverId'")or die(mysqli_error($db));
  $row1 = mysqli_fetch_assoc($sql);
  // Dato importante: la falta se elimina porque el recibo se hizo "mal"; por lo tanto, el sistema de faltas actual (10/03/2019) hace que tenga una falta
  mysqli_query($db, "DELETE FROM faltas WHERE driverId = '$driverId' LIMIT 1");
  echo $compania;

  if($_GET['empresa'] == "1" || $_GET['empresa'] == "50" || $_GET['empresa'] == "76"){ // Outwest, MVT y TruckNexion
      $driver = new DriverMesilla($row1['DriverId']);
      $recibo = new Recibo($driver);
  }else{
      $driver = new Driver($row1['DriverId']);
      $recibo = new Recibo($driver);
  }


  $idEmpresaGET = mysqli_real_escape_string($db, $_GET['empresa']);

  $res = mysqli_query($db, "SELECT COUNT(*) as erroresCount FROM errores_nom WHERE idEmpresa = '$idEmpresaGET'");
  $row = mysqli_fetch_assoc($res);

  ?>
  <script>
  $(function(){
    <?php
    if($row['erroresCount'] > 0){
      ?>
      alert('Existen errores en nómina');
      <?php
    }else{
      ?>
      alert('Nómina hecha con éxito');
      <?php
    }
    ?>
    window.location.href = "../panelErroresNomina.php?idEmpresa=<?= htmlspecialchars($_GET['empresa'], ENT_QUOTES, 'UTF-8') ?>";
  });
</script>
