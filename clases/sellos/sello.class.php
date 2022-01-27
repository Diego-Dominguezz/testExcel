<?php
    class Sello
    {
        protected $db;
        private $id;
        private $payload;
        public $token;

        function __construct($db, $token = null){

            $this->db = $db;
            if($token != null) {
                $this->payload = $token->payload->data;
                $this->token = $token->payload;
            }
        }

        function getData(){
            $stmt = $this->db->query("SELECT * FROM conductores WHERE idConductores = ".$this->payload->id)or die(mysqli_error($this->db));
            $row = $stmt->fetch_assoc();
            $stmtData = $this->db->query("SELECT fechaIngreso FROM trabajadores WHERE nombre = '".$row['Nombre']."' AND apellidoPaterno = '".$row['ApellidoPaterno']."' AND apellidoMaterno = '".$row['ApellidoMaterno']."' AND fechaIngreso = '2020'")or die(mysqli_error($this->db));
            $data = $stmtData->fetch_assoc();
            if($data){
                $row['fechaAlta'] = $data['fechaIngreso']; // Ingreso del conductor a la empresa
            }else{
                $row['fechaAlta'] = date("Y-m-d", $row['fechaAlta']);
            }
            return $row;
        }
    }
