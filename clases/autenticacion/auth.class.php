<?php
	use Firebase\JWT\JWT;
    class Auth{
        private $usuario;
        private $idUsuario;
        private $token;
        private $db;
        private $key = 'KeyFirm123!#';
        private $tiempoActual;
        private $tiempoLimite = 3600;
        private $tiempoMinimoAlerta = 600;


        function __construct ($db){
            $this->db     = $db;
            $this->tiempoActual = time();
        }

        function login($identificador, $contrasena, $force = false){
            session_regenerate_id();
            if(!$force){
                $stmt = $this->db->prepare("SELECT *, usuarios.nombre as nombreUsuario, `roles`.`Nombre` as nombreRol  FROM `usuarios` INNER JOIN `roles`  on `usuarios`.`RolId` = `roles`.`idRoles` WHERE `Correo` = ?")or die(mysqli_error($this->db));
                $stmt->bind_param("s", $identificador);
            }else{
                $stmt = $this->db->prepare("SELECT *, usuarios.nombre as nombreUsuario, `roles`.`Nombre` as nombreRol  FROM `usuarios` INNER JOIN `roles`  on `usuarios`.`RolId` = `roles`.`idRoles` WHERE `idUsuarios` = ?")or die(mysqli_error($this->db));
                $stmt->bind_param("s", $identificador);
            }

            $stmt->execute()or die(mysqli_error($this->db));
            $res = $stmt->get_result();
            if(!$res->num_rows){ // Usuario incorrecto
                return -1;
            }
            $row = $res->fetch_assoc();
            $row = array_change_key_case($row); // Muy molesto aprender los camelCase que no se aplicó correctamente

            if($force){
                $this->iniciarSesiones($row);
                return 1;
            }

            if(password_verify($contrasena, $row['password'])){ // A este paso del login, ya se tuvo que haber tenido las sesiones activas
                $this->iniciarSesiones($row);
                return 1;
            }
            return 0;// Contraseña incorrecta
        }

        function iniciarSesiones($row){
            $this->idUsuario = $row['idusuarios'];
            $this->usuario   = $row['nombreusuario'];
            $_SESSION['logged']         = $row['idusuarios'];
            $_SESSION['cliente']        = $row['clienteid'];
            $_SESSION['correo']         = $row['correo'];
            $_SESSION['rol']            = $row['rolid'];
            $_SESSION['usuario']        = $row['nombreusuario'];
            $_SESSION['nombre_rol']     = $row['nombrerol'];
            $_SESSION['ingles']         = $row['ingles'];
            $_SESSION['sesionIniciada'] = $this->tiempoActual;
        }

        function validarCookie($token){
            try{
                $data = JWT::decode($token, $this->key, array('HS256'));
                switch ($this->login($data->data->id, null, true)) {
                    case -1:
                    case 0: // Cualquiera de los dos casos que me devuelva un usuario que no exista, borrar la cookie
                        setcookie("auth_r", "", time() - 3600, "/", null, null, true);
                        header("Location: login.php");
                        break;
                    case 1:
                        header("Location: index.php");
                        break;
                }
            }catch(Exception $e){
                setcookie("auth_r", "", time() - 3600, "/", null, null, true);
                header("Location: login.php");
            }
        }

        function validarTiempoCookie($token){
            try{
                $data = JWT::decode($token, $this->key, array('HS256'));
            }catch(Exception $e){
                ?>
                <script>
                    window.location.href = "assets/logout.php";
                </script>
                <?php
            }
        }

        function aumentarSesion(){
            $output = [];
            if(isset($_SESSION['logged'])){
                $_SESSION['sesionIniciada'] = $this->tiempoActual;
                $output['response'] = true;
                $output['segundosRestantes'] = $this->tiempoLimite;
            }else{
                $output['response'] = false;
            }
            return json_encode($output);
        }

        function recuerdame(){
            $tiempo = time();
            $token = array(
                'iat' => $tiempo, // Tiempo que inició el token
                'exp' => strtotime("+12 hours"), // 12 horas
                'data' => [ // información del usuario
                    'id' => $_SESSION['logged'],
                    'nombre' => "Prueba"
                ]
            );

            $jwt = JWT::encode($token, $this->key);
            setcookie( "auth_r", $jwt, strtotime("+12 hours"), "/", null, null, true );
        }

        function intentosDesconocido(){ // Conteo de incidentes en los últimos 10 minutos de usuarios sin existencia
            $stmt = $this->db->prepare("SELECT COUNT(*) as conteo FROM logs WHERE fecha >= ? AND fecha <= ? AND accionId = ?");


            $minutoAtras = date("Y-m-d H:i:s", strtotime("-10 minutes"));
            $minutoActual = date("Y-m-d H:i:s");
            $accionId = 5;
            $stmt->bind_param("ssi", $minutoAtras, $minutoActual, $accionId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();

            if($row['conteo'] > 2){
                return true;
            }

            return false;
        }

        function validarCaptcha($token){
            $request = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LeU38gUAAAAAAUSpe_imSmZzVSxVZk1fnN_qkqt&response=".$token);
            $json = json_decode($request);
            return $json;
        }

        function validarSesion(){
            $this->aumentarSesion();

            if (isset($_COOKIE['auth_r'])) {
                $this->validarTiempoCookie($_COOKIE['auth_r']);
                return;
            }

            $segundosRestantes = ($_SESSION['sesionIniciada'] + $this->tiempoLimite) - time();
            if($segundosRestantes <= 0){
                ?>
                <script>
                    window.location.href = "assets/logout.php";
                </script>
                <?php
                    return;
            }
            $this->alertaSesionAcabada($segundosRestantes, $segundosRestantes <= $this->tiempoMinimoAlerta);
        }

        function alertaSesionAcabada($segundosRestantes, $terminando){
            ?>
            <div class="modal fade" id="alertaSesionAcabada" tabindex="-1" role="dialog" aria-labelledby="alertaSesionAcabadaLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                           Su sesión está a punto de expirar
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cerrarSesionAlert" class="btn btn-secondary">Cerrar sesión</button>
                            <button type="button" id="seguirConectado" class="btn btn-success" data-dismiss="modal">Seguir conectado (<span id="segunderoRestante"><?= $segundosRestantes ?></span>)</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>

            <?php

            if($terminando){
                ?>
                $("#alertaSesionAcabada").modal();
                <?php
            }
                ?>
                $("#seguirConectado").click(function(){
                    fetch("assets/autenticacion/aumentarSesion.php").then(data => data.json()).then((data) => {
                        if(data.response){
                            $("#segunderoRestante").html(data.segundosRestantes);
                        }
                    });
                });
                $("#cerrarSesionAlert").click(function(){
                    window.location.href = "assets/logout.php";
                });
                var interval = setInterval(intervalFuncion, 1000);

                function intervalFuncion() {
                    var numeroRestante = parseInt($("#segunderoRestante").html()) - 1;
                    if(numeroRestante <= <?= $this->tiempoMinimoAlerta ?> && !$("#alertaSesionAcabada").is(':visible')){
                        $("#alertaSesionAcabada").modal();
                    }
                    $("#segunderoRestante").html( numeroRestante );
                    if(numeroRestante == 0){
                        clearInterval(interval);
                        window.location = window.location.href;
                    }
                }

            </script>

            <?php
        }
    }
