<?php
  /**
   *
   */
  class Log extends ConexionDB{

    private $fecha, $autor;
    public $accion;

    function __construct($accion, $autor = NULL) {
      parent::__construct();
      $this->autor = $autor;
      $this->accion = $accion;
      $this->fecha = date("Y-m-d H:i:s");
    }

    function getAutor(){
      if($this->autor->id != "NULL"){
        $sql = "SELECT * FROM usuarios WHERE idUsuarios = ".$this->autor->id;
        $res = mysqli_query($this->db, $sql)or die(mysqli_error($this->db));
        $row = mysqli_fetch_assoc($res);
        return $row['Nombre'];
      }
      return;
    }

    public function imprimir(){
      $mensaje = "[".$this->fecha.", ".$this->autor->ip."] ".$this->getAutor()." ".$this->accion->getTipo();
      if($this->accion->tabla){
        $mensaje.=" ".$this->accion->tabla;
      }
      return $mensaje;
    }

    public function getAccion(){
      return $this->accion->getTipo();
    }

  }

 ?>
