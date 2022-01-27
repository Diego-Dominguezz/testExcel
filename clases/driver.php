<?php
  class Driver extends Conexion{
    public $id, $driverId, $nombre, $apellidoPaterno, $apellidoMaterno, $faltas, $prestamoCantidad, $empresa, $viajes, $bonoRecomendacion, $status, $ajustes, $septimoDia, $pension, $jubilado, $prestamoDolares;

    public function __construct($driverId){
      parent::__construct();
      $this->driverId = $driverId;
      $this->viajes = array();
      $this->datosPersonales();
      $this->getViajes();
      $this->getFaltas();
      $this->prestamoCantidad = 0;
      $this->bonoRecomendacion = 0;
      $this->ajustes = array();

      $this->getAjustes();

      $this->getBonos();
      if(count($this->viajes)){ // Si el usuario tiene viajes que le haga el cobro del prestamo
        $this->getPrestamos();
      }
    }

    private function getAjustes(){
      $driverId = $this->driverId;
      $this->ajustes['deduccion'] = 0;
      $this->ajustes['bono'] = 0;
      $sql = "SELECT * FROM ajustes WHERE driverId = '$driverId' AND (semanaReciboNegativo <> '".$this->_conf->semana."' OR (semanaReciboNegativo IS NULL)) AND (cobrarSemana <= '".$this->_conf->semana."' OR cobrarSemana IS NULL) AND semanaCobrado IS NULL AND anoCobrado IS NULL";
      $res = mysqli_query($this->db, $sql)or die(mysqli_error($this->db));
      if(mysqli_num_rows($res)){
        while ($row = mysqli_fetch_assoc($res)) {
          if($row['dolares'] == "1"){ // Si viene el ajuste en dólares

            $bono = new Viaje(0);
            if($row['tipo']){ // Bono
              $bono->millas = 1;
              $bono->tarifa = $row['pesos'];
              $bono->tipo = 3;
            }else{ // Deducción
              $bono->tarifa = 0;
              $bono->millas = $row['pesos'];
              $bono->tipo = 2;
            }

            $this->viajes[] = $bono;

          }else{ // Si viene el ajuste en pesos
            if(!$row['tipo']){ // Si el tipo es 0 es deducción
              $this->ajustes['deduccion'] += $row['pesos'];
            }else{ // tipo es 1 es bono
              $this->ajustes['bono'] += $row['pesos'];
            }
          }
          $idAjuste = $row['id'];
          mysqli_query($this->db, "UPDATE ajustes SET semanaCobrado = '".$this->_conf->semana."', anoCobrado = '". $this->_conf->ano."' WHERE id = '$idAjuste'")or die(mysqli_error($this->db));
        }

      }

    }

    private function datosPersonales(){ // Función manda a llamar a la base de datos para completar el objecto con sus datos

      $stmt = $this->db->prepare("SELECT idConductores, Nombre, ApellidoPaterno, ApellidoMaterno, DriverId, Compania, Status, septimoDia, pension, jubilado FROM conductores WHERE DriverId = ? ORDER BY idConductores DESC LIMIT 1") or trigger_error($this->db->error);
      $stmt->bind_param("s", $this->driverId);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($this->id, $this->nombre, $this->apellidoPaterno, $this->apellidoMaterno, $this->driverId, $empresa, $this->status, $this->septimoDia, $this->pension, $this->jubilado);
      $stmt->fetch();
      $stmt->close();
      if(!$this->id){
//	print_r($this);
        throw new Exception("Error trayendo los id ".$this->driverId);
      }

      if($this->status == 0){ // Hacer que el conductor se haga de reingreso
        $stmt = $this->db->prepare("UPDATE conductores SET Status = 2 WHERE idConductores = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->close();
        #region mensaje
        $message = '<!doctype html>
                  <html lang="en">
                    <head>
                      <meta charset="utf-8">
                      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                      <style>
                        *{
                          padding: 0;
                          margin: 0;
                          font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                          box-sizing: border-box;
                        }
                        body{
                          width: 100%;
                        }
                        .lbl-leyenda{
                          text-align: center;
                          background-color: rgb(87,242,129);
                          border-radius: 6px;
                          border: solid 2px;
                          border-color: #B8C4DB !important;
                          width: 100%;
                          padding: 7px;
                          margin-top: 7px;
                          margin-bottom: 7px;
                        }
                        .header-correo{
                          margin-left: 0;
                          margin-right: 0;
                          display: inline-block;
                          background-color: black;
                          color: white;
                            width: 100%;
                        }
                        .favicon{
                          margin-top: 50%;
                          max-width: 100%;
                          max-height: 100%;
                          margin: 14.5% auto;
                          display: block;
                          margin-left: 12px;
                        }
                        .col-status{
                          float: right;
                          margin: 5px;
                          padding: 25px;
                          text-align: right;
                        }
                        .col-favicon{
                          float: left;
                        }
                        .body-correo{
                          max-width: 1200px;
                          color: black;
                          padding: 10px;
                        }
                        .body-mail{
                          background-color: #FAFAFA;
                        }
                        .body-travel-status{
                          text-align: center;
                          background-color: #EAECF0;
                          border-radius: 6px;
                          border: solid 1px;
                          border-color: #B8C4DB !important;
                          width: 100%;
                          height: 100%;
                          padding-bottom: 12px;
                          margin-bottom: 12px;
                          padding-top: 12px;
                        }
                        .img-status{
                          width: 80%;
                          padding-top: 10px;
                          margin-bottom: 10px;
                        }
                        .body-travel{
                          width: 100%;
                          justify-content: center;
                          text-align: center;
                        }
                        .body-travel-origen,.body-travel-destino{
                          display: inline-block;
                          padding-top: 12px;
                          background-color: #EAECF0;
                          border-radius: 6px;
                          border: solid 1px;
                          border-color: #B8C4DB !important;
                          width: 47.5%;
                          height: 100%;
                          padding-bottom: 12px;
                          text-align: center;
                          justify-content: center;
                        }
                        .body-travel-origen{
                          margin-right: 12px;
                        }
                        .body-travel-destino{
                          margin-left: 12px;
                          margin-top: 12px;
                          margin-bottom: 12px;
                        }
                        .body-driver-info{
                          background-color: #EAECF0;
                          border-radius: 6px;
                          border: solid 1px;
                          border-color: #B8C4DB !important;
                          width: 100%;
                          height: 100%;
                          padding-bottom: 12px;
                          margin-top: 12px;
                          margin-bottom: 12px;
                          padding-top: 12px;
                        }
                        .img-profile-img{
                          height: 70%;
                        }
                        .body-truck{
                          justify-content: center;
                          text-align: center;
                          display: inline;
                        }
                        .body-truck-info-details{
                          background-color: #EAECF0;
                          border-radius: 6px;
                          border: solid 1px;
                          border-color: #B8C4DB !important;
                          max-width: 800px;
                          padding: 30px;
                          margin-top: 12px;
                          margin-bottom: 12px;
                          text-align: left;
                        }
                        .truck{
                          width: 45%;
                          max-height: 50%;
                          float: right;
                          margin-right: 12px;
                          padding-top: 90px;
                        }
                        .footer-correo{
                          background-color: rgb(87,242,129);
                          width: 100%;
                          height: 120%;
                          text-align: center;
                          display: table;
                        }
                        .footer-text{
                          text-align: center;
                          font-size: 12px;
                          color: rgb(150,150,150);
                          padding: 15px;
                        }
                        .url-company{
                          padding: 15px;
                        }
                        h1 {
                          font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                          font-style: normal;
                          font-variant: normal;
                          font-weight: 700;
                          line-height: 26.4px;
                        }
                        h3 {
                          font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                          font-style: normal;
                          font-variant: normal;
                          font-weight: 700;
                          line-height: 15.4px;
                        }
                        p {
                          font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                          font-style: normal;
                          font-variant: normal;
                          font-weight: 400;
                          line-height: 20px;
                        }
                        blockquote {
                          font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                          font-style: normal;
                          font-variant: normal;
                          font-weight: 400;
                          line-height: 30px;
                        }
                        pre {
                          font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                          font-style: normal;
                          font-variant: normal;
                          font-weight: 400;
                          line-height: 18.5667px;
                        }
                        @media only screen and (max-width: 600px){
                          .header-correo{
                            text-align: center;
                          }
                          .col-favicon{
                            text-align: center;
                            width: 100%;
                          }
                          .favicon{
                            width: 90%;
                            margin-left: 0px;
                            padding-left: 10%;
                            /*padding-right: 5%;*/
                          }
                          .col-status{
                            width: 100%;
                            text-align: center;
                          }
                          .body-correo{
                            width: 100%;
                            color: black;
                            padding: 10px;
                          }
                          .img-status{
                            width: 80%;
                            padding-top: 10px;
                            margin-bottom: 10px;
                          }
                          .body-travel-origen,.body-travel-destino{
                            width: 100%;
                            height: 100%;
                            padding-top: 15px;
                            padding-bottom: 12px;
                          }
                          .body-travel-destino{
                            margin-left: 0px;
                            margin-bottom: 0px;
                          }
                          .body-driver-info{
                            margin-bottom: 0px;
                            height: 100%;
                            margin-top: 15px;
                            padding-bottom: 12px;
                          }
                        }
                        @media only screen and (max-width: 300px){
                          .body-truck-info-details{
                            width: 100%;
                          }
                          .truck{
                            width: 100%;
                            margin-right: 0px;
                            padding-top: 0px;
                          }
                          .body-truck-info-details{
                            height: 100%;
                            padding-bottom: 12px;
                          }
                          .lbl-info-truck{
                            display: inline-block;
                          }
                          .lbl-info-caja{
                            display: inline-block;
                          }
                        }
                      </style>
                    </head>

                    <body>
                    <center>
                        <div class="body-mail" >
                        <div class="header-correo">
                            <div class="col-favicon" >
                              <img class="favicon" src="http://transnorte.com.mx/services/appOld/favicon.png">
                          </div>
                          <div class="col-status">
                            </div>
                        </div>
                        <div class="body-correo">
                            <h2>Conductor inactivo con millas</h2>
                            <div class="body-truck-info-details" style="text-align: center;">
                              <img src="http://transnorte.com.mx/services/transnorth/img/main/userCircle.png" style="height: 100px; width: 100px; border-radius: 50%;"><br><br>
                              <h2><b>'.$this->nombre." ".$this->apellidoPaterno." ".$this->apellidoMaterno.'</b></h2><br>
                              <b>DriverId:</b> '.$this->driverId.'<br>
                              <b>Compañia:</b> '.$empresa.'<br>
                            </div>
                        </div>
                      </center>
                    </body>
              <footer>
                <div class="container">
                  <p class="footer-text">
                    Este correo electrónico ha sido enviado porque las preferencias de correo electrónico se configuraron para recibir actualizaciones de nomina.<br>No respondas a este correo electrónico. No podemos responder las consultas que se envíen a esta dirección. Para obtener respuesta inmediata a tus preguntas llama a nuestros operadores.<br>Copyright © 2019 Transnorte. S. de R.L. de C.V. Todos los derechos reservados.
                  </p>
                </div>
              </footer>
            </html>
        ';
        #endregion

        $this->enviarCorreo(array("omarmontoya@keyinnovation.org", "mgamboa@transnorte.com.mx"), "Conductor inactivo con millas", $message);
      }

      $stmt = $this->db->prepare("SELECT idClientes FROM clientes WHERE nombre = ?") or trigger_error($this->db->error);
      $stmt->bind_param("s", $empresa);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($this->empresa);
      $stmt->fetch();
      $stmt->close();
    }

    private function getFaltas(){ // Función manda a llamar todas las faltas del usuario
      $stmt = $this->db->prepare("SELECT COUNT(*) as faltas FROM faltas WHERE driverId = ? AND (semanaCobrado IS NULL AND anoCobrado IS NULL) AND ((cobrarSemana IS NULL OR cobrarSemana <= ?) AND (cobrarAno IS NULL OR cobrarAno <= ?))")or die(mysqli_error($this->db)); // Saca las faltas que aun no se cobran
      $stmt->bind_param("ssi", $this->driverId, $this->_conf->semana,  $this->_conf->ano);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($this->faltas);
      $stmt->fetch();
      $stmt->close();
      $stmt = $this->db->prepare("UPDATE faltas SET semanaCobrado = ?, anoCobrado  = ? WHERE driverId = ? AND (semanaCobrado IS NULL AND anoCobrado IS NULL) AND ((cobrarSemana IS NULL OR cobrarSemana <= ?) AND (cobrarAno IS NULL OR cobrarAno <= ?))"); // Hace update de las faltas a cobrar
      $stmt->bind_param("sissi", $this->_conf->semana, $this->_conf->ano, $this->driverId,$this->_conf->semana, $this->_conf->ano);
      $stmt->execute()or die(mysqli_error($this->db));
      $stmt->close();
    }

    private function getBonos(){ // bono recomendación
      $stmt = $this->db->prepare("SELECT idbonos, cantidad FROM bonos WHERE semanaPagado IS NULL AND anoPagado IS NULL AND driverid = ?")or die(mysqli_error($this->db));
      $stmt->bind_param("s", $this->driverId);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($idBono, $cantidad);
      $this->bonoRecomendacion = 0;
      while ($row = $stmt->fetch()) {
        $cambiarStatusBono = $this->db->prepare("UPDATE bonos SET semanaPagado = ?, anoPagado = ? WHERE idbonos = ?")or die(mysqli_error($this->db));
        $cambiarStatusBono->bind_param("sii", $this->_conf->semana, $this->_conf->ano, $idBono);
        $cambiarStatusBono->execute()or die(mysqli_error($this->db));
        $this->bonoRecomendacion += $cantidad; // Asignación de la cantidad al miembro de este objeto
        $cambiarStatusBono->close();
      }
      $stmt->close();

    }

    private function getPrestamos(){ // Función manda a llamar los prestamos del usuario

        $cobrosRestantes = $cuotasTotales = $totalPrestamo = 0;
        $stmt = $this->db->prepare("SELECT cobrosRestantes, cuotasTotales, totalPrestamo, ultimoCobroSemana, ultimoCobroAno, dolares, id FROM prestamos WHERE driverId = ? AND activo = 1 AND fechaCobrarSemana <= ? AND fechaCobrarAno <= ?")or die(mysqli_error($this->db));
        // Obtiene un prestamo mayor al día de hoy correspondiente al driver id, que aun este activo
        $stmt->bind_param("ssi", $this->driverId, $this->_conf->semana, $this->_conf->ano);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($cobrosRestantes, $cuotasTotales, $totalPrestamo, $ultimoCobroSemana, $ultimoCobroAno, $prestamoDolares, $id);
        $this->prestamoCantidad = [];
        while ( $stmt->fetch() ) {
          if(!$cobrosRestantes){
            return;
          }

          $this->prestamoCantidad[] = [$totalPrestamo / $cuotasTotales, $prestamoDolares];
          $cobrosRestantes--;
          if(($ultimoCobroAno == $this->_conf->ano) && ($ultimoCobroSemana == $this->_conf->semana)){
            return;
          }
          if($cobrosRestantes == 0){
            $stmt1 = $this->db->prepare("UPDATE prestamos SET activo = 0, cobrosRestantes = 0, ultimoCobroSemana = ?, ultimoCobroAno = ? WHERE id = ?");
            $stmt1->bind_param("sii", $this->_conf->semana, $this->_conf->ano, $id);
            $stmt1->execute();
          }else{
            $stmt1 = $this->db->prepare("UPDATE prestamos SET cobrosRestantes = ?, ultimoCobroSemana = ?, ultimoCobroAno = ? WHERE id = ?");
            $stmt1->bind_param("isii", $cobrosRestantes, $this->_conf->semana, $this->_conf->ano, $id);
            $stmt1->execute();
          }
          $stmt1->close();

        }

        $stmt->close();

    }

    private function getViajes(){
      $sql = mysqli_query($this->db, "SELECT * FROM nom WHERE driverid = '".$this->driverId."' AND semana = '".$this->_conf->semana. "' AND ano = '" . $this->_conf->ano . "'");
      while($row = mysqli_fetch_assoc($sql)){
        $this->viajes[] = new Viaje($row['id']);
      }

      if(count($this->viajes) && $this->status == 0){
        // mysqli_query($this->db, "UPDATE conductores SET Status = 1 WHERE DriverId = '".$this->driverId."'");
      }



      if ($_GET['empresa'] == "1") {

        $sql ="SELECT *, if(entrenador = '$this->driverId', 0, 1) as tipo FROM `relacionesEntrenamiento` WHERE (entrenador = '$this->driverId' OR alumno = '$this->driverId') AND semana = ". date("W", strtotime("last sunday"))." AND ano = ".date("Y", strtotime("last sunday"));
        $resRelaciones = mysqli_query($this->db, $sql)or die(mysqli_error($this->db));
        while ($rowRelaciones = mysqli_fetch_assoc($resRelaciones)) {

          foreach ($this->viajes as $viaje) {

            if(strtotime($rowRelaciones['fechaInicio'])  <=  strtotime( $viaje->fechaInicio ) && (strtotime( $viaje->fechaFinal ) <= strtotime($rowRelaciones['fechaFinal']) || $rowRelaciones['fechaFinal'] == "0000-00-00" ) && ($viaje->tipo == 0 || $viaje->tipo == 1)){
              $viaje->entrenamiento = true;
              if ($rowRelaciones['tipo'] == 0) { // Es entrenador
                $viaje->tarifa = $rowRelaciones['rateEntrenador'];
              }else{ // Es alumno
                $viaje->tarifa = $rowRelaciones['rateAlumno'];
              }
            }
          }

        }


      }


    }
}
