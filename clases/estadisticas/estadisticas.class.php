<?php
    class Estadisticas{
        private $db;
        public $output = [];

        function __construct ($db){
            $this->db = $db;
            $this->output['conductores']           = $this->getTotalConductores();
            $this->output['empresasTerminadas']    = $this->getEmpresasNominaTerminadas();
            $this->output['viajes']                = $this->getViajes();
            $this->output['tipoCambio']            = $this->getTipoCambio();
            $this->output['totalNominaUltimoMes']  = $this->getTotalNominaUltimoMes();
            $this->output['totalNominaActual']     = $this->getNominaActual();
            $this->output['totalNominaDosMeses']   = $this->getTotalNominaDosMeses();
            $this->output['conductorLongevo']      = $this->getConductorMasViejoActivo();
            $this->output['faltas']                = $this->getFaltas();
            $this->output['ultimoMovimiento']      = $this->getUltimoMovimiento();
            $this->output['viajesInternacionales'] = $this->getViajesInternacionales();
        }

        function getViajesInternacionales(){
            $stmt = $this->db->query("SELECT SUM(TipoEnvio = 0) importacion, SUM(TipoEnvio = 1) exportacion FROM viajes");
            $row = $stmt->fetch_assoc();
            return $row;
        }

        function getUltimoMovimiento(){
            $stmt = $this->db->query("SELECT fecha, Nombre, accion, tabla as accionFinal FROM logs INNER JOIN usuarios ON usuarios.idUsuarios = logs.autor INNER JOIN acciones_logs ON logs.accionId = acciones_logs.id ORDER BY logs.fecha DESC LIMIT 1");
            return $stmt->fetch_assoc();
        }

        function getNominaActual(){
            $output = [];
            $sql = "SELECT SUM(pagoNeto) as pagoNeto FROM recibos WHERE (semana = ".date("W",strtotime("-1 sunday"))." AND year = ".date("Y",strtotime("-1 sunday")).") OR (semana = ".date("W",strtotime("-2 sunday"))." AND year = ".date("Y",strtotime("-2 sunday")).") OR (semana = ".date("W",strtotime("-3 sunday"))." AND year = ".date("Y",strtotime("-3 sunday")).") OR (semana = ".date("W",strtotime("-4 sunday"))." AND year = ".date("Y",strtotime("-4 sunday")).")";
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch_assoc();
            $output['format'] = "$".number_format((float) $row['pagoNeto'], 2, '.', ',');
            $output['total'] = $row['pagoNeto'];
            $output['estatus'] = $output['total'] > $this->output['totalNominaUltimoMes']['total'];
            return $output;
        }

        function getFaltas(){
            $output = [];
            $stmt = $this->db->query("SELECT count(*) contador, semana, ano FROM faltas GROUP BY semana, ano ORDER BY id DESC LIMIT 10")or die(mysqli_error($this->db));
            if(!mysqli_num_rows($stmt)){
                return [];
            }
            while ($row = $stmt->fetch_assoc()) {
                $output['semana'][] = $row['semana'];
                $output['contador'][] = $row['contador'];
            }
            if(isset($output['semana'])){
                $output['semana'] = array_reverse($output['semana']);
                $output['contador'] = array_reverse($output['contador']);
                return $output;
            }


            return [];
        }
    
        function getTotalNominaUltimoMes(){
            $output = [];
            $sql = "SELECT SUM(pagoNeto) as pagoNeto FROM recibos WHERE (semana = ".date("W",strtotime("-5 sunday"))." AND year = ".date("Y",strtotime("-5 sunday")).") OR (semana = ".date("W",strtotime("-6 sunday"))." AND year = ".date("Y",strtotime("-6 sunday")).") OR (semana = ".date("W",strtotime("-7 sunday"))." AND year = ".date("Y",strtotime("-7 sunday")).") OR (semana = ".date("W",strtotime("-8 sunday"))." AND year = ".date("Y",strtotime("-8 sunday")).")";
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch_assoc();
            $output['format'] = "$".number_format((float) $row['pagoNeto'], 2, '.', ',');
            $output['total'] = $row['pagoNeto'];
            return $output;
        }
        
        function getTotalNominaDosMeses(){
            $sql = "SELECT SUM(pagoNeto) as pagoNeto FROM recibos WHERE (semana = ".date("W",strtotime("-9 sunday"))." AND year = ".date("Y",strtotime("-9 sunday")).") OR (semana = ".date("W",strtotime("-10 sunday"))." AND year = ".date("Y",strtotime("-10 sunday")).") OR (semana = ".date("W",strtotime("-11 sunday"))." AND year = ".date("Y",strtotime("-11 sunday")).") OR (semana = ".date("W",strtotime("-12 sunday"))." AND year = ".date("Y",strtotime("-12 sunday")).")";
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch_assoc();
            // $output['format'] = "$".number_format((float) $row['pagoNeto'], 2, '.', ',');
            $resultado = $row['pagoNeto'];
    
            if($this->output['totalNominaUltimoMes']['total'] < $resultado){
                return false; // La cantidad actual es menor al mes pasado
            }else{
                return true; // La cantidad actual es mayor
            }
            
        }
    
        function getTipoCambio(){
            $output = [];
            $stmt = $this->db->query("SELECT * FROM archivos ORDER BY id DESC");
            for ($i=0; $i < 2; $i++) { 
                $row = $stmt->fetch_assoc();
                if($row){
                    $output[$i]['total'] = $row['tipoCambio'];
                    $output[$i]['format'] = "$".number_format((float) $row['tipoCambio'], 2);
                }
            }
            return $output;
        }
    
        function getTotalConductores(){
            $output = [];
            $stmt = $this->db->query("SELECT SUM(Status = 0) as inactivos, SUM(Status = 1 OR Status = 2) as activos, SUM(Status = 3) as permisoPersonal, SUM(Status = 4) as permisoVisa, SUM(Status = 5) as vacaciones, SUM(Status = 6) as incapacidad, COUNT(*) as total FROM conductores")or die(mysqli_error($this->db));
            $row = $stmt->fetch_assoc();
            return $row;
        }
    
    
        function getEmpresasNominaTerminadas(){
            $sql = "SELECT idEmpresa FROM recibos WHERE semana = ".date("W", strtotime("last sunday"))." AND year = ".date("Y", strtotime("last sunday"))." GROUP BY idEmpresa";
            $stmt = $this->db->query($sql)or die(mysqli_error($this->db));
            return mysqli_num_rows($stmt);
        }
    
        function getConductorMasViejoActivo(){
            $stmt = $this->db->query("SELECT DriverId as driverId, CONCAT(Nombre, ' ', ApellidoPaterno, ' ', ApellidoMaterno) as nombre FROM conductores WHERE Status = 1 OR Status = 2 ORDER BY idConductores");
            return $stmt->fetch_assoc();
        }
        
        function getViajes(){
            $ultimoMes = strtotime("last month");
            $stmt = $this->db->query("SELECT SUM(TipoEnvio = 1 OR TipoEnvio = 2) internacionales, SUM(TipoEnvio = 0) locales, count(*) contador FROM viajes WHERE fechaInicioViaje >= ".$ultimoMes);
            $row = $stmt->fetch_assoc();
            return $row;
        }
    
        function getConductoresTabla(){
            $stmt = $this->db->query("SELECT * FROM conductores ORDER BY idConductores DESC LIMIT 3");
            
            ?>
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>Driver ID</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
            <?php
            while ($row = $stmt->fetch_assoc()) {
            ?>
                <tr>
                    <td><?= $row['DriverId'] ?></td>
                    <td><?= ucwords(strtolower($row['Nombre']." ".$row['ApellidoPaterno'])) ?></td>
                    <td><?= $row['Compania'] ?></td>
                    <td><?= date("d-m-Y", $row['fechaAlta']) ?></td>
                </tr>
            <?php
            }
            ?>
            </table>
        <?php }
    }