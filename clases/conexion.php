<?php
  class Conexion{
    public $db, $_conf;

    public function __construct(){
      require("./assets/db.php");
      require_once("./assets/conf.php");
      $_conf = new Configuracion();
      $this->db = $db;
      $this->_conf = $_conf;
    }

    public function log($error){
      insertarLog($error); // Se puede invocar la función insertar log porque en el global en clases/index.php está incluido el archivo
    }

    public function enviarCorreo($correos, $subject, $message){
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

      $mail->addAddress("services@transnorte.com.mx");
      foreach ($correos as $key => $correo) {
        $mail->addAddress($correo);
      }

      $mail->Subject = $subject;
      $mail->Body = $message;
      // $mail->send();
    }

  }
 ?>
