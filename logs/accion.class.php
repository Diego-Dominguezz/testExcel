<?php
  /**
   *
   */
  class Accion extends ConexionDB{

    private $tipo; // Inserción, eliminación, actualización, otro
    public $tabla;

    function __construct($tipo, $tabla = NULL) {
      parent::__construct();
      $this->tipo = $tipo;
      $this->tabla = strtoupper($tabla);
    }

    function getTipo(){
      $sql = "SELECT * FROM acciones_logs WHERE id = ".$this->tipo;
      $res = mysqli_query($this->db, $sql)or die(mysqli_error($this->db));
      if(mysqli_num_rows($res)){
        $row = mysqli_fetch_assoc($res);
        return $row['accion'];
      }
      return "OTRO, ID: ".$this->tipo. mysqli_num_rows($res);
    }

  }

 ?>
