<?php
class Viaje extends Conexion{

	public $millas, $tarifa, $orderid, $tipo, $descripcion, $fechaInicio, $fechaFinal, $id, $entrenamiento;

	public function __construct($id) {
		parent::__construct();
		if($id){
			$this->getDatos($id);
			$this->id = $id;
		}
		$this->entrenamiento = false;
	}

	 public function __get($key){return $this->$key;}

	 public function __set($key,$value){$this->$key = $value;}

	 private function getDatos($id){
		 $stmt = $this->db->prepare("SELECT millas, tarifa, tipopago, orderid, tipotarifa, fechaInicio, fechaTermino FROM nom WHERE id = ?");
		 $stmt->bind_param("i", $id);
		 $stmt->execute();
		 $stmt->store_result();
		 $stmt->bind_result( $this->millas, $this->tarifa, $this->descripcion, $this->orderid, $this->tipo, $this->fechaInicio, $this->fechaFinal );
		 $stmt->fetch();
		 $stmt->close();
	 }
}
?>
