<?php
    use Firebase\JWT\JWT;

    class Posicionamiento{
        private $token;
        private $db;
        private $key;
        public $dataViaje;
        private $idViaje;
        
        function __construct($db, $token = null){
            $this->db  = $db;
            $this->key = base64_encode('KeyFirm123!@#');
            if($token != null){
                $this->token = $token;
            }
        }

        function getConductores($data){
            $output = [];
            $data = json_decode($data);
            foreach ($data as $key => $dataApi) {
                $sql = "SELECT conductores.img, conductores.idConductores, conductores.DriverId, conductores.Nombre, conductores.ApellidoPaterno, conductores.ApellidoMaterno, conductores.Compania, conductores.idCompania FROM conductores WHERE DriverId = '".mysqli_real_escape_string($this->db, $dataApi->driverId)."'";
                $stmt = $this->db->query($sql)or die(mysqli_error($this->db));
                while ($row = $stmt->fetch_assoc()) {
                    $output[] = $row;
                    $output[$key]['fecha'] = "Hace ".$this->getTiempoAtras($dataApi->posicion->fecha);
                }
            }

            return $output;
        }

        function getTiempoAtras($time){
            $time = strtotime($time);

            $time = time() - $time; // to get the time since that moment
            $time = ($time < 1) ? 1 : $time;
            $tokens = array(
                31536000 => 'año',
                2592000 => 'mes',
                604800 => 'semana',
                86400 => 'dia',
                3600 => 'hora',
                60 => 'minuto',
                1 => 'segundo'
            );

            foreach($tokens as $unit => $text) {
                if ($time < $unit) continue;
                $numberOfUnits = floor($time / $unit);
                $textoFinal = $numberOfUnits.' '.$text.(($numberOfUnits > 1) ? $text == 'mes' ? 'es' : 's' : '');

                if($text == "segundo"){
                    $textoFinal = "un momento";
                }

                return $textoFinal;
            }
        }

        function getTokenIndividual($driverId){
            $tiempo = time();
            $tok = array(
                'iat' => $tiempo, // Tiempo que inició el token
                'exp' => strtotime("+1 hour"), // 1 hora
                'data' => array("driverid" => $driverId)
            );
            return JWT::encode($tok, $this->key);
        }

        function getValores(){
            $token = JWT::decode($this->token, $this->key, array('HS256'));
            $this->idViaje = $token->data->id;
            $this->getViajeData();
        }

        function getSocketToken($idUsuario){
            $this->dataViaje['idUsuarioSniffer'] = $idUsuario;
            return $this->getPayload($this->dataViaje);
        }

        function getPayloadTokenIndividual($tokenRecibido){
            try{
                $token = JWT::decode($tokenRecibido, $this->key, array('HS256'));
                if(!isset($token->data->driverid)){
                    return false;
                }
                return $tokenRecibido;
            }catch(Exception $err){
                return false;
            }
        }

        function getTokenPayload($tokenRecibido){
            $token = JWT::decode($tokenRecibido, $this->key, array('HS256'));
            return $token->data;
        }

        function getViajeData(){ // Código del archivo en php/ChecarDetallesViaje.php
            //query para obtener detalles del viaje deseado
            $sql = $this->db->prepare("SELECT
                    `a`.
                    `idViajes`, `a`.
                    `EnvioId`, `a`.
                    `NumeroCaja`, `a`.
                    `DireccionOrigen`, `a`.
                    `DireccionDestino`, `b`.
                    `idConductores`, `b`.
                    `DriverId`, `b`.
                    `Nombre`, `b`.
                    `ApellidoPaterno`, `b`.
                    `ApellidoMaterno`, `b`.
                    `TelefonoMovil`, `c`.
                    `idCamiones`, `c`.
                    `Modelo`, `c`.
                    `Placa`, `c`.
                    `NumeroCamion`, `a`.
                    `TipoEnvio`, `a`.
                    `CajaPlaca`
                    FROM `viajes`
                    AS `a`
                    INNER JOIN `conductores`
                    AS `b`
                    ON `a`.
                    `ConductoresId` = `b`.
                    `idConductores`
                    INNER JOIN `camiones`
                    AS `c`
                    ON `a`.
                    `CamionId` = `c`.
                    `idCamiones`
                    WHERE `idViajes` = ? ") or trigger_error($db->error);
                    if ($sql) {
                        $sql-> bind_param("i", $this->idViaje);
                        $sql->execute();
                        $sql-> store_result();
                        $sql-> bind_result($idViajes, $EnvioId, $NumeroCaja, $DireccionOrigen, $DireccionDestino, $idConductores, $DriverId, $Nombre, $Ap, $Am, $Telefono, $idCamiones, $modelo, $placa, $NumeroCamion, $tipo, $cajaPlaca);
                        $sql-> fetch();
                        if ($tipo = 1) {
                            $tipoViaje = "Exportacion";
                        } else {
                            $tipoViaje = "Importacion";
                        }
                        $this->dataViaje = array(
                            'id' => $idViajes, 'envioid' => $EnvioId, 'numerocaja' => $NumeroCaja, 'dir_origen' => $DireccionOrigen, 'dir_destino' => $DireccionDestino,
                            'idconductores' => $idConductores, 'driverid' => $DriverId, 'nombre' => $Nombre, 'ap' => $Ap, 'am' => $Am, 'telefono' => $Telefono,
                            'idcamiones' => $idCamiones, 'modelo' => $modelo, 'placa' => $placa, 'numerocamion' => $NumeroCamion, 'tipoviaje' => $tipoViaje, 'cajaplaca' => $cajaPlaca,
                            'tipoenvio' => $tipo
                        );
                    }
        }

        function getPayload($token){
            $tiempo = time();
            $tok = array(
                'iat' => $tiempo, // Tiempo que inició el token
                'exp' => strtotime("+1 hour"), // 1 hora
                'data' => $token
            );
            return JWT::encode($tok, $this->key);
        }

        function getUltimosViajes(){
            $output = [];
            $stmt = $this->db->query("SELECT * FROM viajes WHERE Status <> '0' AND ConductoresId = '".$this->dataViaje['idconductores']."' ORDER BY idViajes DESC LIMIT 5");
            while ($row = $stmt->fetch_assoc()) {
                $output[] = $row;
            }
            return $output;
        }

    }