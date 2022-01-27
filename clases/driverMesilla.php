<?php
  class DriverMesilla extends Driver{

    private $rateAlone, $rateTeam, $sinRates, $viajesAlone, $viajesTeam, $pagoTransnorte, $viajesEntrenamiento;

    public function __construct($driverId){
      parent::__construct($driverId);
      $this->sinRates = false;
      $this->viajesEntrenamiento = array();
      $this->getRates();
      if(!$this->rateAlone || !$this->rateTeam){ $this->sinRates = true; }
      $this->viajesAlone = 0;
      $this->viajesTeam = 0;
      $this->clasificarViajes();
      $this->pagoTransnorte = 0; // Como en transnorte se le paga menos, hay que inicializar el pago en 0
      if(!$this->sinRates){
        $this->robarDriver();
      }
    }

    private function robarDriver(){
      $this->pagoTransnorte += $this->viajesAlone * $this->rateAlone;
      $this->pagoTransnorte += $this->viajesTeam * $this->rateTeam;
      
      foreach ($this->viajesEntrenamiento as $key => $viaje) {
        $this->pagoTransnorte += $viaje->millas * $viaje->tarifa;
      }

    }

    private function getRates(){
      $stmt = $this->db->prepare("SELECT MillasAlone, MillasTeam FROM conductores WHERE DriverId = ?") or trigger_error($this->db->error);
      $stmt->bind_param("s", $this->driverId);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($this->rateAlone, $this->rateTeam);
      $stmt->fetch();
      $stmt->close();
    }

    private function clasificarViajes(){
      foreach ($this->viajes as $key => $viaje) {
        if(!$viaje->entrenamiento){
          switch ($viaje->tipo) {
            case 0: // Viaje alone
            $this->viajesAlone += $viaje->millas;
            break;
            case 1: // Viaje team
            $this->viajesTeam += $viaje->millas;
            break;
          }
        }else{
          $this->viajesEntrenamiento[] = $viaje;
        }

      }
    }

    public function __get( $key ){         return $this->$key;} // get de todos los atributos
    public function __set($key, $value){   $this->$key = $value;} // set de todos los atributos

  }
 ?>
