<?php
  class ConexionDB{
    public $db;

    public function __construct(){
      require(dirname(__FILE__)."/../assets/db.php");
      $this->db = $db;
      // if($_SERVER['SERVER_NAME'] == "localhost"){
      //   $this->db = new mysqli("192.168.0.114:3307", "keyinnovation", "KeyConsulting1", "transnorte")or die(mysqli_error($this->db));
      // }else{
	    //   $this->db = mysqli_connect("localhost", "transnorth", "TransNorth#18", "transnorth");
      // }
    }
  }
 ?>
