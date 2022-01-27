<?php
  use Firebase\JWT\JWT;

  class Token
  {
    public $id;
    public $string, $payload;
    private $key = "DP#bb!SnRuNuP8";

    function __construct($payload)
    {
      $this->key = base64_encode($this->key);
      if(is_array($payload)){
        $this->payload = $payload;
        $this->string = $this->encriptar();
      }else{
        $this->string = $payload;
        $this->payload = $this->desencriptar();
      }
    }

    function encriptar(){
      try{
        return JWT::encode($this->payload, $this->key);
      }catch(Exception $err){
        throw new Exception("No se puede encodear", 1);
      }
    }

    function desencriptar(){
      try{
        return JWT::decode($this->string, $this->key, array('HS256'));
      }catch(Exception $err){
        if(get_class($err) == 'Firebase\JWT\ExpiredException'){
          throw new Exception("Token caduco", 3);
        }else{
          throw new Exception("Token sin validez", 2);
        }
      }
    }

  }

 ?>
