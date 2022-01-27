<?php
class Recibo extends Conexion{

  private $pagoBruto;
  private $ingles;
  private $seguro;
  private $retencionImpuestos;
  private $deducciones;
  private $bonos;
  private $semana;
  private $pagoNeto;
  private $dolar;
  private $prestamo;
  private $infonavit;
  private $avances;
  private $fonacot;
  private $retenciones;
  private $pensionAlim;
  private $otrasRetenciones;
  private $ano;
  private $driver;
  private $encontradoNomipaq;
  private $netoNomipaq;

  public function __construct($driver){
    parent::__construct();
    $this->driver = $driver;
    $this->pagoBruto = 0;
    $this->pagoNeto = 0;
    $this->bonos = 0;
    $this->deducciones = 0;
    $this->prestamo = 0;

    $diaDetectado = date("Y-m-d");
    $this->semana = $this->_conf->semana;
    $this->ano = $this->_conf->ano;


    $this->encontradoNomipaq = false;
    $this->calcularRetenciones();
    $this->calcularFaltas();
    $this->calcularPrestamo();
    $this->calcularPagoBruto();
    $this->calcularEstatus();
    $this->calcularPagoNeto();
    if(!count($this->driver->viajes)){
      $this->insertarFalta();
    }

    $error1 = false;
    $error2 = false;


    $idEmpresa = $this->driver->empresa;


    if(!$this->encontradoNomipaq){
      $idDriver = $this->driver->id;
      if($this->driver->status){
        $errorNombre = mysqli_query($this->db, "SELECT * FROM errores_nom WHERE idUsuario = '$idDriver' AND tipoError = '1'");
        if(!mysqli_num_rows($errorNombre)){ // Si no hay errores, insertar el error
          mysqli_query($this->db, "INSERT INTO errores_nom (idUsuario, tipoError, fechaDetectado, idEmpresa) VALUES ($idDriver, '1', '$diaDetectado', '$idEmpresa')")or die("Sergio: ".mysqli_error($this->db));
        }
      }
      $error1 = true;
    }

    if(get_class($this->driver) == "DriverMesilla"){
      if($this->driver->sinRates){
        $id = $this->driver->id;
        if($this->driver->status){
          $errorRate = mysqli_query($this->db, "SELECT * FROM errores_nom WHERE idUsuario = '$id' AND tipoError = '2'");
          if(!mysqli_num_rows($errorRate)){
            mysqli_query($this->db, "INSERT INTO errores_nom (idUsuario, tipoError, fechaDetectado, idEmpresa) VALUES ($id, 2, '$diaDetectado', $idEmpresa)");
          }
        }
        $error2 = true;
      }
    }

    if($this->pensionAlim || $this->driver->pension){ // Si trae pensión en nomipaq o si trae pensión en la base de datos
      $this->calcularPension();
    }

    if(!$error1 && !$error2){ // Si no tiene error de nomipaq ni error de sin rates se publica
      if(!count($this->driver->viajes)){
        return;
      }
      $driverId = $this->driver->driverId;

      $res = mysqli_query($this->db, "SELECT * FROM recibos WHERE driverid = '$driverId' AND semana = '$this->semana' AND year = '$this->ano'");
      if(!mysqli_num_rows($res)){
        $this->publicarRecibo();
      }

    }

  }

  private function calcularEstatus(){ // Función que separa a los permisos y faltas dependiendo del estado
    // 1. Inactivo, 2. Activo, 3. Reingreso, 4. Permiso personal, 5. Permiso por vida, 6. Vacaciones, 7. Incapacidad
    // $sql = "SELECT * FROM permisos_conductores WHERE idConductor = ".$this->driver->id;
    // $stmt = $this->db->query($sql);
    // $row = $stmt->fetch_assoc();
    // print_r($row);
    $this->calcularVacaciones();
    $this->calcularIncapacidad();
  }

  private function calcularIncapacidad(){
    // Función para calcular los días habiles dependiendo de la fecha a fecha de incapacidad
    $sql = "SELECT * FROM vacaciones_conductores WHERE incapacidad = 1 AND eliminado IS NULL AND idConductor = " . $this->driver->id;
    $stmt = $this->db->query($sql);
    $row = $stmt->fetch_assoc();
    if (!isset($row)) {
      return false;
    }
    if ($this->driver->empresa == '9' || $this->driver->empresa == '9') { // FIXME INCAPACIDAD
      $inicioSemana = new DateTime(date("Y-m-d", strtotime("last sunday -2 monday")));
    } else {
      $inicioSemana = new DateTime(date("Y-m-d", strtotime("last sunday -1 monday")));
    }
    $finalSemana  = new DateTime(date("Y-m-d", strtotime("last sunday")));
    $diasHabiles = $this->calcularDiasHabiles($inicioSemana, $finalSemana, new DateTime($row['inicio']), new DateTime($row['final']));
    if($diasHabiles !== false){
      $unidadImpuestos = $this->retencionImpuestos / 7;
      $this->retencionImpuestos = $unidadImpuestos * $diasHabiles;
      $this->ingles = 0;
      $this->infonavit = 0;
      $this->otrasRetenciones = 0;
      $this->seguro = 0;
      $this->retenciones = 0;
    }
  }

  private function calcularVacaciones(){
    // Función para calcular los días habiles dependiendo de la fecha a fecha de vacaciones
    $sql = "SELECT * FROM vacaciones_conductores WHERE incapacidad = 0 AND eliminado IS NULL AND idConductor = ".$this->driver->id;
    $stmt = $this->db->query($sql);
    $row = $stmt->fetch_assoc();
    if(!isset($row)){
      return false;
    }
    // Hay que detectar si la fecha de vacaciones está entre la fecha actual
    $inicioVacaciones = new DateTime($row['inicio']);
    $finalVacaciones  = new DateTime($row['final']);

    // Necesito saber las fechas de la semana pasada, para compararlas con las vacaciones,
    // ya que se paga a partir de la semana pasada, no la semana actual
    if($this->driver->empresa == '9' || $this->driver->empresa == '9'){
      $inicioSemana = new DateTime(date("Y-m-d", strtotime("last sunday -2 monday")));
    }else{
      $inicioSemana = new DateTime(date("Y-m-d", strtotime("last sunday -1 monday")));
    }
    $finalSemana  = new DateTime(date("Y-m-d", strtotime("last sunday")));

    $diasHabiles = $this->calcularDiasHabiles($inicioSemana, $finalSemana, $inicioVacaciones, $finalVacaciones);

    if ($diasHabiles !== false) {
      $unidadImpuestos = $this->retencionImpuestos / 7;
      $this->retencionImpuestos = $unidadImpuestos * $diasHabiles;
    }
  }

  private function calcularDiasHabiles($inicioSemana, $finalSemana, $inicioVacaciones, $finalVacaciones){
    // V = Vacaciones
    // S = Semana

    // S V V S - Semana inicia, vacaciones inician, vacaciones terminan y termina la semana
    // V S V S
    // V S S V
    // S V S V - Semana inicia, vacaciones inician, semana termina y terminan vacaciones

    if( 
      ($inicioSemana->getTimestamp() < $inicioVacaciones->getTimestamp() && $finalSemana->getTimestamp() < $inicioVacaciones->getTimestamp())
   || ($inicioVacaciones->getTimestamp() < $inicioSemana->getTimestamp() && $finalVacaciones->getTimestamp() < $inicioSemana->getTimestamp())
     ){
      // Las vacaciones no corresponden al periodo actual
      return false;
    }

    switch (true) {
      case ($inicioSemana->getTimestamp() <= $inicioVacaciones->getTimestamp()) && ($finalVacaciones->getTimestamp() <= $finalSemana->getTimestamp()): // Inicia la semana y en medio de la semana empiezan las vacaciones y terminan antes de el fin de semana
        // S V V S
        // Hay que contar los días de inicio vacaciones a final de vacaciones
        $diasHabiles = $inicioSemana->diff($inicioVacaciones)->format("%a") + $finalVacaciones->diff($finalSemana)->format("%a");
        break;
      case ($inicioVacaciones->getTimestamp() <= $inicioSemana->getTimestamp()) && ($finalVacaciones->getTimestamp() <= $finalSemana->getTimestamp()): // Inicio de vacaciones y las vacaciones terminan antes de la próxima semana
        // V S V S
        // Hay que contar los días de inicio semana a final de vacaciones
        $diasHabiles = $inicioSemana->diff($finalVacaciones)->format("%a");
      break;
      case ($inicioVacaciones->getTimestamp() <= $inicioSemana->getTimestamp()) && ($finalSemana->getTimestamp() <= $finalVacaciones->getTimestamp()): // Vacaciones completamente, normalmente no va a aplicarse este caso
        // V S S V
        // En esta opción las vacaciones no tienen día hábil, ya que no trabajó en días hábiles
        // Si trabajó en ocasión de vacaciones, la multiplicación de impuestos saldría en 0
        $diasHabiles = 0;
      break;
      case ($inicioSemana->getTimestamp() <= $inicioVacaciones->getTimestamp()) && ($finalSemana->getTimestamp() <= $finalVacaciones->getTimestamp()): // Inicio de semana, vacaciones y las vacaciones terminan después de la semana actual
        // S V S V
        // Tomar el inicio de semana a el inicio de vacaciones, ya que los demás días no trabajó
        $diasHabiles = $inicioSemana->diff($inicioVacaciones)->format("%a");
        break;
      default:
        $diasHabiles = 0;
        break;
    }

    return $diasHabiles;
  }

  private function calcularPension(){
    if((float)$this->driver->pension > 1){ // Cantidad estática
      $pensionFinal = $this->driver->pension;
      // $this->netoNomipaq -= $this->driver->pension / 2;
    }elseif((float)$this->driver->pension != 0 && (float)$this->driver->pension <= 1){ // Cantidad por porcentaje
      $pensionFinal = $this->pagoNeto * $this->driver->pension;
    }else{
      $this->pensionAlim = 0;
      $this->log(11);
      return;
    }
    $this->pensionAlim = $pensionFinal;
    $this->pagoNeto -= $pensionFinal;
  }

  private function publicarRecibo(){
    $stmt = $this->db->prepare("INSERT INTO recibos (
      driverid,
      pagobruto,
      impuestos,
      seguro,
      avances,
      avances2,
      avances3,
      infonavit,
      fonacot,
      clasesIngles,
      retenciones,
      prestamo,
      pensionAlim,
      pagoNeto,
      extras,
      semana,
      idEmpresa,
      year,
      nomina) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )")or die(mysqli_error($this->db));
    $stmt->bind_param("ssssssssssssssssiid",
        $this->driver->driverId,
        $this->pagoBruto,
        $this->retencionImpuestos,
        $this->seguro,
        $this->avances,
        $this->avances,
        $this->avances,
        $this->infonavit,
        $this->fonacot,
        $this->ingles,
        $this->deducciones, // Retencioens va a ser deducciones, Viajes en negativo
        $this->prestamo,
        $this->pensionAlim,
        $this->pagoNeto,
        $this->bonos, // Extras va a ser los bonos o viajes positivos
        $this->semana,
        // $this->driver->empresa, // Este esta bugeado, porque puede traer el id de la empresa duplicada
        $_GET['empresa'],
        $this->ano,
        $this->netoNomipaq
    );

    $this->pagoBruto = round($this->pagoBruto, 2);
    $this->retencionImpuestos = round($this->retencionImpuestos, 2);
    $this->seguro = round($this->seguro, 2);
    $this->avances = round($this->avances, 2);
    $this->avances = round($this->avances, 2);
    $this->avances = round($this->avances, 2);
    $this->infonavit = round($this->infonavit, 2);
    $this->fonacot = round($this->fonacot, 2);
    $this->ingles = round($this->ingles, 2);
    $this->deducciones = round($this->deducciones, 2);
    $this->prestamo = round($this->prestamo, 2);
    $this->pensionAlim = round($this->pensionAlim, 2);
    $this->pagoNeto = round($this->pagoNeto, 2);
    $this->bonos = round($this->bonos, 2);
    $this->bonos;

    if((float)$this->pagoNeto < 0){ // Insertar un ajuste por falta de millaje
      $pagoNetoReal = abs($this->driver->ajustes['deduccion'] - abs($this->pagoNeto));
      $sql = "SELECT * FROM ajustes WHERE driverId = '".$this->driver->driverId."' AND pesos = ". $pagoNetoReal . " AND tipo = 0 AND semanaReciboNegativo = '".$this->_conf->semana."' AND anoReciboNegativo = '".$this->_conf->ano."'";
      $res = mysqli_query($this->db, $sql);
      if(!mysqli_num_rows($res)){
        mysqli_query($this->db, "INSERT INTO ajustes (driverId, pesos, tipo, semanaReciboNegativo, anoReciboNegativo) VALUES ('".$this->driver->driverId."', ".abs($this->pagoNeto).", 0, '".$this->_conf->semana."', '".$this->_conf->ano."')")or die(mysqli_error($this->db));
      }
      $this->pagoNeto = 0;
    }

    $stmt->execute();

  }

  public function insertarFalta(){
    $stmt = $this->db->prepare("INSERT INTO faltas (driverId, semana, ano, idEmpresa) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", 
    $this->driver->driverId, 
    $this->_conf->semana, 
    $this->_conf->ano, 
    $this->driver->empresa);

    $driverId = $this->driver->driverId;
    if(!mysqli_num_rows(mysqli_query($this->db, "SELECT * FROM faltas WHERE semana = '". $this->_conf->semana."' AND ano = '".$this->_conf->ano."' AND driverId = '$driverId'"))){ // Ingresa una falta nueva si no existe
      $stmt->execute();
    }

  }

  private function calcularPagoNeto(){
    $this->bonos *= $this->dolar; // Los bonos hay que multiplicarlos por el dolar antes de subirlos al recibo
    $this->pagoNeto = $this->pagoBruto + $this->bonos;
    // $this->pagoNeto = ($this->pagoBruto + $this->bonos);// * $this->dolar;
    $this->pagoNeto -= $this->ingles;
    if(get_class($this->driver) == "DriverMesilla" || $_GET['empresa'] == '50' ){ // Solo a mesilla se le cobra el seguro
      $this->pagoNeto -= $this->seguro;
    }
    $this->pagoNeto -= $this->infonavit;
    $this->pagoNeto -= $this->retencionImpuestos;
    $this->pagoNeto -= $this->prestamo;
    $this->pagoNeto -= $this->deducciones;


    $this->ano = $this->_conf->ano;

    $this->avances = 0;
    $this->fonacot = 0;
    $this->retenciones = 0;
    // $this->pensionAlim = 0;
    $this->otrasRetenciones = 0;
    $this->bonos += $this->driver->bonoRecomendacion; // Bono recomendación esta en pesos, por lo tanto, se suma después de cambiarlo a pesos

    // Suma de bonos de recomendación a el pago neto
    $this->pagoNeto += $this->driver->bonoRecomendacion;

    // Ajustes
    $bono = $this->driver->ajustes['bono'];
    $deduccion = $this->driver->ajustes['deduccion'];

    $this->bonos += $bono;
    $this->deducciones += $deduccion;
    $this->pagoNeto += $bono;
    $this->pagoNeto -= $deduccion;
    // Ajustes

  }

  private function calcularPrestamo(){
    $this->prestamo = 0;
    
    if($this->driver->prestamoCantidad == 0){
      // print_r($this->driver->prestamoCantidad);
      // echo $this->driver->driverId;
      return;
    }

    foreach ($this->driver->prestamoCantidad as $key => $prestamo) {
      if($prestamo[1]){ // Si es en dólares
        $this->prestamo += $prestamo[0] * $this->dolar;
      }else{
        $this->prestamo += $prestamo[0];
      }
    }
    // if($this->driver->prestamoCantidad){
    //   $this->prestamo = $this->driver->prestamoCantidad;
    //   if($this->driver->prestamoDolares){
    //     $this->prestamo *= $this->dolar;
    //   }
    // }
  }

  private function calcularFaltas(){
    if($this->driver->faltas == 0){
      return;
    }



    // Las faltas de CDSI y Melher siempre se deben cobrar por una sola, tal si fuera una falta normal, ya que el precio de cada uno está declarado antes
    // calcularRetenciones() declara ingles como 250 si es Melher o Cdsi, por lo tanto si tiene falta, solamente se cobra por faltas mas el actual cobro

     // switch ($this->driver->empresa) {
     //   case "9": // Melher es quincenal
     //   case "19": // CDSI es catorcenal

    $this->ingles             *= $this->driver->faltas + 1;
    $this->seguro             *= $this->driver->faltas + 1;
    $this->retencionImpuestos *= $this->driver->faltas + 1;

    if($_GET['empresa'] == "1"){ // Si es mesilla se cobra sólo una vez si la falta ya estuvo en este mismo mes
      $fechaSemana = date("Y-m-d", strtotime("last sunday"));
      $fechaCobrado = strtotime("last sunday");
      $resFaltas = mysqli_query($this->db, "SELECT * FROM faltas WHERE driverId = '".$this->driver->driverId."' AND cobrado = 1");
      $faltaEnMes = 0;
      while ($row = mysqli_fetch_assoc($resFaltas)) {
        if(date("m", $row['fecha']) == date("m", strtotime("last sunday"))){
          $faltaEnMes++;
        }
      }

      if($faltaEnMes == 1){ // Si son solamente una falta en el mes, que cobren las faltas correspondientes
        $this->infonavit *= $this->driver->faltas + 1;
      }
      // Si tiene mas de una sola falta al mes, el infonavit se cobra solamente 1 vez
    }else{
      $this->infonavit *= $this->driver->faltas + 1;
    }

    
    
    // break;

       // default:
       //   $this->ingles *=              $this->driver->faltas + 1;
       //   $this->seguro *=              $this->driver->faltas + 1;
       //   $this->retencionImpuestos *=  $this->driver->faltas + 1;
       //    break;
     // }
  }

  private function calcularPagoBruto(){

      foreach ($this->driver->viajes as $key => $value) { // Por cada viaje que hay dentro del objeto Driver
        if(($value->millas == 1 || $value->millas == "") && $value->tarifa > 1){ // Bonos
          if($value->millas == ""){
            $value->millas = 1;
          }
  				$this->bonos += $value->millas * $value->tarifa;
  			}elseif ($value->millas > 1 && $value->tarifa == 1) { // Deducciones ** Puede ser un OR **
          $this->deducciones += abs($value->millas);
          
  			}else{ // Viajes comunes
          if(get_class($this->driver) == "DriverMesilla"){ // Carlitos el saltarín
            $this->pagoBruto = $this->driver->pagoTransnorte * $this->dolar; // Revisar archivo driverMesilla
          }else{ // Viajes comunes
            $this->pagoBruto += $value->millas * $value->tarifa;
          }

  			}
      }


      if(get_class($this->driver) != "DriverMesilla"){ // Wacha la línea indicada por la palabra clave Carlitos el saltarín
        $this->pagoBruto *= $this->dolar;
      }
      $this->deducciones *= $this->dolar; // Las deducciones al principio son dolares, para mostarlos en el recibo los multiplico por el dolar

  }

  private function calcularRetenciones(){
    $replace = [
  		'&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
  		'&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
  		'&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
  		'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
  		'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
  		'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
  		'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
  		'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
  		'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K',
  		'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
  		'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
  		'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
  		'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
  		'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
  		'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
  		'&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
  		'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
  		'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
  		'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
  		'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
  		'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
  		'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
  		'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
  		'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
  		'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
  		'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
  		'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
  		'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
  		'&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
  		'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
  		'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
  		'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
  		'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
  		'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
  		'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
  		'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
  		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
  		'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '',
  		'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a',
  		'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
  		'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
  		'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
  		'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
  		'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
  		'ю' => 'yu', 'я' => 'ya'
  	];

    $idEmpresa = $_GET['empresa'];
    $nomipaq = "";
    $stmt = $this->db->prepare("SELECT archivo, tipoCambio FROM archivos WHERE semana = ? AND ano = ? AND idEmpresa = ?")or die(mysqli_error($this->db));
    $stmt->bind_param("sii", $this->_conf->semana, $this->_conf->ano, $idEmpresa);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($nomipaq, $this->dolar);
    $stmt->fetch();
    $stmt->close();

    $deduccionesSQL = mysqli_query($this->db, "SELECT cantidad FROM deducciones")or die(mysqli_error($this->db));
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);

    $impuestosMayor = $deduccionesRes['cantidad'];
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);
    $impuestosMenor = $deduccionesRes['cantidad'];
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);
    $this->ingles = $deduccionesRes['cantidad'];
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);
    if( $this->driver->empresa == 9 || $this->driver->empresa == 1 || $_GET['empresa'] == 1 || $_GET['empresa'] == 50){
      $this->seguro = $deduccionesRes['cantidad'] * $this->dolar;
    }else{
      $this->seguro = 0;
    }
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);
    $impuestosMenorMelher = $deduccionesRes['cantidad'];
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);
    $impuestosMayorMelher = $deduccionesRes['cantidad'];
    $deduccionesRes = mysqli_fetch_assoc($deduccionesSQL);
    $impuestosMenorEMS = $deduccionesRes['cantidad'];

    if(!file_exists("../".$nomipaq) || !$nomipaq){
      $nombreArchivo = "nomipaq/".date("d-m-y", strtotime("sunday"))."-000000-".$idEmpresa.".xlsx";
      if(!$nomipaq && file_exists("../".$nombreArchivo)){ // No existe el registro pero si el archivo
        $resArchivos = $this->db->query("SELECT * FROM archivos ORDER BY id DESC");
        $rowArchivos = $resArchivos->fetch_assoc();
        $sql = "INSERT INTO archivos (usuario, archivo, tipoCambio, semana, ano, idEmpresa) VALUES ('AntiBug', '$nombreArchivo', '" . $rowArchivos['tipoCambio'] . "', '" . $this->_conf->semana . "', '" . $this->_conf->ano . "', '$idEmpresa')";
        $this->db->query($sql)or die(mysqli_error($this->db));
        $nomipaq = $nombreArchivo;
      }else{
        throw new Exception("No existe nomipaq de esta empresa.");
      }
    }
    $cuatrocientosSeptimoDia = false;
    try{
        $Spreadsheet = new SpreadsheetReader("../".$nomipaq);
        $Sheets = $Spreadsheet -> Sheets();
        $Spreadsheet -> ChangeSheet(0);
        foreach ($Spreadsheet as $Key => $Row){
                    if($Key === 6){
                      foreach ($Row as $keys => $values) {
                        $encabezado = str_replace(array_keys($replace), $replace, $values);
                        $encabezado = strtoupper($encabezado);
                        switch ($encabezado) {
                          case "PRESTAMO INFONAVIT (VSM)":
                            $infonavit1 = $keys;
                            break;
                          case "PRESTAMO INFONAVIT (CF)":
                            $infonavit2 = $keys;
                            break;
                          case "PRESTAMO INFONAVIT (FD)":
                            $infonavit3 = $keys;
                            break;
                          case "PRESTAMO INFONAVIT":
                            $infonavit4 = $keys;
                            break;
                          case "SEPTIMO DIA":
                            $keySeptimo = $keys;
                            break;
                          case "*NETO*":
                            $keyNeto = $keys;
                            break;
                          case "PENSION ALIMENTICIA":
                            $keyPension = $keys;
                            break;
                          case "CODIGO":
                            $keyCodigo = $keys;
                            break;
                        }
                      }
                    }
        
                    if ($Row){
          
                      if(is_numeric($Row[0])){
            
                        $choferIdArray = explode(" ", $Row[1]);
                        $choferFix = array();
                        foreach ($choferIdArray as $key => $value){
                          if($value){
                            $choferFix[] = $value;
                          }
                        }
                        $nombrePegado = implode(" ", $choferFix); // Limpio el nombre de cualquier espacio doble, y lo pego nuevamente
                        $nombre1 = str_replace(array_keys($replace), $replace, trim($this->driver->apellidoPaterno)." ".trim($this->driver->apellidoMaterno)." ".trim($this->driver->nombre));
                        $nombre2 = strtoupper(str_replace(array_keys($replace), $replace, $nombrePegado)); // Nombre de nomipaq
                        $nombre1 = $this->trimearNombres($nombre1);
                        similar_text($nombre1, $nombre2, $porcentaje);
                        if($porcentaje >= 90 ){ // El nombre tiene que estar en mayusculas **LO ENCONTRÓ**
                          $this->setIdNomipaq($Row[$keyCodigo]);

                          $septimoDia = str_replace("\$", "", $Row[$keySeptimo]);
                          $resSeptimo = mysqli_query($this->db, "SELECT septimoDia FROM conductores WHERE idConductores = '".$this->driver->id."'");
                          $rowSeptimo = mysqli_fetch_assoc($resSeptimo);
                          $stmt = $this->db->prepare("UPDATE conductores SET septimoDia = ? WHERE idConductores = ?");
                          $stmt->bind_param("di", $septimoDia, $this->driver->id);
                          if($septimoDia != "0" && $this->driver->septimoDia == "0"){
                            $stmt->execute();
                          }elseif ($septimoDia == "0" && $rowSeptimo['septimoDia'] != "0") { // Si el nomipaq no tiene el dato y la base de datos si lo tiene
                            $septimoDia = $this->driver->septimoDia;
                          }else{ // Los dos tienen valor
                            if($septimoDia != $this->driver->septimoDia){
                              if($septimoDia < $this->driver->septimoDia){
                                $septimoDia = $this->driver->septimoDia;
                              }
                              $stmt->execute();
                            }

                          }

              

                          if($septimoDia >= "400"){ 
                            
                            $this->retencionImpuestos = $impuestosMayor * $this->dolar;
                            $cuatrocientosSeptimoDia = true;
                            if($septimoDia < "800" && $this->driver->empresa == "9"){ // Si es de Melher y gana menos de 800 pesos el septimo día
                              $cuatrocientosSeptimoDia = false; // 18/12/2019: El día de ayer, Fabiola mandó un correo diciendo que Melher debe de tener el cobro del séptimo día siempre y cuando gane mas de 800 pesos el septimo día, ya que melher se paga por quincena
                            }
                          }else{
                            $this->retencionImpuestos = $impuestosMenor * $this->dolar;
                          }

              
                          if(!isset($keyPension)){
                            $this->pensionAlim = false;
                          }else{
                            if((float) str_replace("\$", "", $Row[$keyPension]) != 0){
                              $this->pensionAlim = true;
                            }
                          }

                          
                          
                          if(!isset($infonavit4)){
                            $infonavit4 = 0;
                            $Row[$infonavit4] = 0;
                          }
                          if(!isset($infonavit3)){
                            $infonavit3 = 0;
                            $Row[$infonavit3] = 0;
                          }
              
                          if(!isset($infonavit2)){
                            $infonavit2 = 0;
                            $Row[$infonavit2] = 0;
                          }
                          if(!isset($infonavit1)){
                            $infonavit1 = 0;
                            $Row[$infonavit1] = 0;
                          }
              
                          $this->infonavit =  str_replace("\$", "", $Row[$infonavit1])  + str_replace("\$", "", $Row[$infonavit2]) + str_replace("\$", "", $Row[$infonavit3]) + str_replace("\$", "", $Row[$infonavit4]);
                          // Hay que quitar el signo de pesos en el nomipaq y sumar los tres valores
                          // Tengo en mente que hay alguna empresa que no tiene los tres datos de infonavit, va a marcar error si es así

                          $this->encontradoNomipaq = true;

                          $this->netoNomipaq = floatval(ltrim($Row[$keyNeto], '$'));;
                          break;
                        }else{
                        }
                      }
                    }else{
                      var_dump($Row);
                    }
        }//termina for each
    }
    catch (Exception $E){
      echo $E -> getMessage();
    }


    switch ($this->driver->empresa) { // Empresas quincenales
      case '9': // Melher
        $this->ingles *= 2;
        $this->seguro = 0;
        $this->retencionImpuestos = $impuestosMenorMelher;

        if($cuatrocientosSeptimoDia){
          // $this->retencionImpuestos = 4155.31; // Jovana confirmó de que esta era la cantidad
          $this->retencionImpuestos = $impuestosMayorMelher * $this->dolar; // Correo enviado el 17/12/2019 por Fabiola. El septimo día de más de 800 pesos se cobra por dólar *******?????????
        }
        
        break;
      case '19': // CDSI
        $this->ingles *= 2;
        $this->retencionImpuestos *= 2;
        break;

      case '85': // EMS SERROT INC.
        $this->retencionImpuestos = $impuestosMenorEMS * $this->dolar; // ! Aún no hay impuestos de cantidad mayor a 400 pesos
        $this->ingles = 0;
        $this->seguro = 0;
        break;
    }

    if($this->driver->jubilado){ // La retención de impuestos lo pongo al final porque puede que sea de melher o CDSI
      $this->retencionImpuestos /= 2;
    }

  }

  private function setIdNomipaq($idNomipaq){
    $idNomipaq = mysqli_real_escape_string($this->db, $idNomipaq);
    $this->db->query("UPDATE conductores SET idContpaq = $idNomipaq WHERE idConductores = ".$this->driver->id)or die("ERRROR CONTIPAQ: ".mysqli_error($this->db));
    // print_r($idNomipaq);
    // echo "UPDATE conductores SET idContipaq = '$idNomipaq' WHERE idConductores = " . $this->driver->id;
  }

  private static function trimearNombres($nombre){
		$trimNombre = explode(" ", $nombre);
		$nombreFinal = array();

		foreach ($trimNombre as $key => $value)
			if($value)
				$nombreFinal[] = $value;

		return implode(" ", $nombreFinal);
	}

}
?>
