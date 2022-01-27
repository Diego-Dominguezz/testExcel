<?php 
    class Clientes {
        private $db;
        public $output;

        function __construct($db) {
            $this->db = $db;
            $this->output['clientes'] = $this->getClientes();
        }

        public function getClientes() {
            $stmt = $this->db->query("SELECT clientes.idClientes, clientes.nombre FROM clientes");
            $output = [];
            while($row = $stmt->fetch_assoc()) {
                $output[] = $row;
            }
            return $output;
        }
    }
?>