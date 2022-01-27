<?php
    $ruta = __DIR__."/logs_request.txt";
    if(!isset($_SESSION)){
      session_start();
    }
    if(!file_exists($ruta)){
      fopen($ruta, "wr");
    }
    if(!empty($_REQUEST)){
      $contenido = file_get_contents($ruta);
      $file = fopen($ruta, "w");
      $data = "[".$_SERVER['REMOTE_ADDR'].", ".date("Y-m-d H:i:s")."] (".$_SESSION['logged'].", ".$_SESSION['usuario'].") ".$_SERVER['HTTP_REFERER']."\n".json_encode(debug_backtrace()[1]['file'])."\n".json_encode($_REQUEST)."\n\n";
      $contenido = $data.$contenido;
      fwrite($file, $contenido);
      fclose($file);
    }