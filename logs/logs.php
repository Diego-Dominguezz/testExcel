<?php
  require(__DIR__."/../assets/db.php");
  require("conexion.class.php");
  require("accion.class.php");
  require("log.class.php");
  require("autor.class.php");

  // $idUsuario = mysqli_real_escape_string($db, $_REQUEST['idUsuario']);
  if(!isset($_SESSION)){
    session_start();
  }
  if(isset($_SESSION['logged'])){
    $idUsuario = $_SESSION['logged'];
  }else{
    $idUsuario = "NULL";
  }




  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['accion']) && isset($_REQUEST['insertarLog'])) {
    if(isset($_REQUEST['tabla'])){
      $tabla = mysqli_real_escape_string($db, $_REQUEST['tabla']);
    }else{
      $tabla = NULL;
    }
    $accion = mysqli_real_escape_string($db, $_REQUEST['accion']);

    if(isset($_POST['usuario'])){
      $usuario = mysqli_real_escape_string($db, $_REQUEST['usuario']);
      $res = mysqli_query($db, "SELECT * FROM usuarios WHERE Correo = '$usuario'");
      $row = mysqli_fetch_assoc($res);
      $idUsuario = $row['idUsuarios'];
    }

    if($idUsuario)
      insertarLog($accion, $tabla);
  }

  function insertarLog($accion, $tabla = NULL){ // Acción es un número, mirar accion.class.php
    global $db, $idUsuario;
    if(isset($_SESSION['logged'])){
      $idUsuario = $_SESSION['logged'];
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    if(isset($_REQUEST['contrasena'])){
      unset($_REQUEST['contrasena']);
    }
    if(isset($_REQUEST['captcha-token'])){
      unset($_REQUEST['captcha-token']);
    }
    if(isset($_REQUEST['imagen'])){
      unset($_REQUEST['imagen']);
    }
    if(isset($_REQUEST['envio'])){
      unset($_REQUEST['envio']);
    }

    $tabla.= "\n\t".json_encode($_REQUEST); 

    $log = new Log(new Accion($accion, $tabla), new Autor($idUsuario, $ip) );

    if(
      $accion == 6 ||
      $accion == 10 ||
      $accion == 8
    ){ // Si es rehacer nómina o hacer nómina, mandar correo
      include_once __DIR__.'/../assets/vendor/autoload.php';
      $mail = new PHPMailer\PHPMailer\PHPMailer(true);
      $mail->isSMTP();                     // Set mailer to use SMTP
      $mail->CharSet = 'UTF-8';
      $mail->Host = 'transnorte.com.mx';  				// Specify main and backup SMTP servers
      $mail->SMTPAuth = true;                             // Enable SMTP authentication
      $mail->Username = 'services@transnorte.com.mx';      // SMTP username
      $mail->Password = 'TechInno18#';                    // SMTP password
      $mail->SMTPSecure = 'ssl';                          // Enable TLS encryption, `ssl` also accepted
      $mail->Port = 465;                                  // TCP port to connect to
      $mail->isHTML(true);
      $mail->setFrom("services@transnorte.com.mx", 'Transnorte nomina');

      $subject = "Movimientos dentro del sistema";
      $message = $log->imprimir();

      $mail->addAddress("services@transnorte.com.mx");

      $mail->Subject = $subject;
      $mail->Body = $message;
      if($_SERVER['SERVER_NAME'] != "localhost"){ // Si es de localhost que no mande el correo
        // $mail->send();
      }
    }


    if($tabla != ""){
      $tablaObj = $log->accion->tabla;
      if($idUsuario != "NULL"){ // Es para identificar si el usuario ingresado existe
        $sql = "INSERT INTO logs (autor, tabla, accionId, fecha, IP) VALUES ('$idUsuario', '".$tablaObj."', '$accion', '".date("Y-m-d H:i:s")."', '".$ip."')";
      }else{
        $sql = "INSERT INTO logs (autor, tabla, accionId, fecha, IP) VALUES ($idUsuario, '".$tablaObj."', '$accion', '".date("Y-m-d H:i:s")."', '".$ip."')";
      }
      // mysqli_query($db, $sql)or die(mysqli_error($db));
    }else{
      $sql = "INSERT INTO logs (autor, accionId, fecha, IP) VALUES ('$idUsuario', '$accion', '".date("Y-m-d H:i:s")."', '".$ip."')";
      // mysqli_query($db, $sql)or die(mysqli_error($db));
    }

    insertarLogTxt($log);
  }

  function insertarLogDesconocido($usuario){ // Log desconocido en UsuarioID
    global $db, $idUsuario;
    if(isset($_SESSION['logged'])){
      $idUsuario = $_SESSION['logged'];
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    $accion = "INTENTO: \"$usuario\"";

    $log = new Log(new Accion($usuario, $accion), new Autor($idUsuario, $ip) );

    $fecha = date("Y-m-d H:i:s");
    $idAccion = 5;

    $stmt = $db->prepare("INSERT INTO logs (tabla, accionId, fecha, IP) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $accion, $idAccion, $fecha, $ip);
    // $stmt->execute();


  }


  function insertarLogTxt($log){
    $ruta = __DIR__."/logs.txt";
    if(!file_exists($ruta)){
      fopen($ruta, "wr");
    }
    $contenido = file_get_contents($ruta);
    $file = fopen($ruta, "w");
    fwrite($file, $log->imprimir()."\nDATA:".json_encode($_REQUEST)."\n\n".$contenido);
    fclose($file);
  }


 ?>
