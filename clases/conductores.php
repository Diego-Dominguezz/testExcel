<?php 
    class Conductores extends Conexion {
        private $semana;
        private $ano;

        public function __construct(){
            parent::__construct();
            $this->semana = $this->_conf->semana;
            $this->ano = $this->_conf->ano;
        }

        public function getConductorId($id) {
            $stmt = $this->db->query("SELECT idConductores FROM conductores WHERE DriverId = '".$id."'");
            $row = $stmt->fetch_assoc();
            if(isset($row['idConductores'])) {
                return $row['idConductores'];
            }
        }

        public function formatearDriverId($id) {
            $obj = str_replace(" ", "", $id);
            $obj = strtoupper($obj);
            return $obj;
        }

        public function compararTextos($este, $conEste) {
            if( similar_text(strtoupper($este), $conEste, $percent) && $percent >= 80) {
                return true;
            } else {
                return false;
            }
        }

        public function deleteRegistros($idEmpresa) {
            $this->db->query("DELETE ajustes.* FROM ajustes INNER JOIN conductores ON ajustes.driverId = conductores.DriverId WHERE cobrarSemana = '$this->semana' AND cobrarAno = '$this->ano' AND conductores.idCompania = '$idEmpresa'")or die(mysqli_error($this->db));
            $this->db->query("DELETE FROM faltas WHERE idEmpresa = '$idEmpresa' AND semana = '$this->semana' AND ano = '$this->ano'")or die(mysqli_error($this->db));
            $this->db->query("DELETE prestamos.* FROM prestamos INNER JOIN conductores ON prestamos.driverId = conductores.DriverId WHERE fechaCobrarSemana = '$this->semana' AND fechaCobrarAno = '$this->ano' AND cuotasTotales = cobrosRestantes AND conductores.idCompania = '$idEmpresa'")or die(mysqli_error($this->db));
            $this->db->query("DELETE bonos.* FROM bonos INNER JOIN conductores ON bonos.driverid = conductores.DriverId WHERE semanaInsertado = '$this->semana' AND anoInsertado = '$this->ano' AND conductores.idCompania = '$idEmpresa'")or die(mysqli_error($this->db));
            $this->db->query("DELETE permisos_conductores.* FROM permisos_conductores INNER JOIN conductores ON permisos_conductores.idConductor = conductores.idConductores WHERE semana = '$this->semana' AND ano = '$this->ano' AND conductores.idCompania = '$idEmpresa'")or die(mysqli_error($this->db));
            // $db->query("DELETE FROM vacaciones_conductores INNER JOIN conductores ON vacaciones_conductores.idConductor = conductores.idConductores WHERE semana = '$this->semana' AND ano = '$this->ano' AND conductores.idCompania = '$idEmpresa'");
            
        }
        
        public function insertFaltas($driverId, $idEmpresa) {
            $sql = "INSERT INTO faltas(driverId, semana, ano, idEmpresa) values ('$driverId', '$this->semana', '$this->ano', '$idEmpresa')";
            if( mysqli_query($this->db, $sql) ) {
                $msj = array("ok"=>true, "mensaje"=>"se inserto la Falta a la base de datos.");
            } else {
                $msj = array("ok"=>false, "mensaje"=>"no se inserto el driverID ".$driverId. " en las Faltas.");
            }
            return json_encode($msj);
        }
        
        public function insertAjustes($driverId, $value) {
            if( $this->compararTextos($value[2], 'BONO') ) {
                $tipo = 1;
            } else {
                $tipo = 0;
            }
            if($value[3] != '' || $value[3] != null) {
                $dolares = 1;
            } else {
                $dolares = 0;
            }
            $sql = "INSERT INTO ajustes(driverId, pesos, tipo, dolares, cobrarSemana, cobrarAno) values ('$driverId', '$value[1]', '$tipo', '$dolares', '$this->semana', '$this->ano')";
            if( mysqli_query($this->db, $sql) ) {
                $msj = array("ok"=>true, "mensaje"=>"se inserto el Ajuste a la base de datos.");
            } else {
                $msj = array("ok"=>false, "mensaje"=>"no se inserto el driverID ".$driverId. " en los Ajustes.");
            }
            return json_encode($msj);
        }

        public function insertarPrestamos($driverId, $value) {
            if( $this->compararTextos($value[3], 'PRESTAMO') ) {
                $tipo = 0;
            } else {
                $tipo = 1;
            }
            if($value[4] != '' || $value[4] != null) {
                $dolares = 1;
            } else {
                $dolares = 0;
            }
            $sql = "INSERT INTO prestamos(driverId, cuotasTotales, totalPrestamo, cobrosRestantes, activo,  tipo, dolares, fechaCobrarSemana, fechaCobrarAno) values ('$driverId', '$value[1]', '$value[2]', '$value[1]', '1' ,'$tipo', '$dolares', '$this->semana', '$this->ano')";
            if( mysqli_query($this->db, $sql) ) {
                $msj = array("ok"=>true, "mensaje"=>"se inserto el Prestamo a la base de datos.");
            } else {
                $msj = array("ok"=>false, "mensaje"=>"no se inserto el driverID ".$driverId. " en los Prestamos.");
            }
            return json_encode($msj);
        }

        public function insertarBonos($driverId, $value) {
            $sql = "INSERT INTO bonos(driverId, cantidad, semanaInsertado, anoInsertado) values ('$driverId', '$value[1]', '$this->semana', '$this->ano')";
            if( mysqli_query($this->db, $sql) ) {
                $msj = array("ok"=>true, "mensaje"=>"se inserto el Bono a la base de datos.");
            } else {
                $msj = array("ok"=>false, "mensaje"=>"no se inserto el driverID ".$driverId. " en los Bonos.");
            }
            return json_encode($msj);
        }

        public function insertarPermisos($conductorId) {
            $sql = "INSERT INTO permisos_conductores(idConductor, semana, ano) values ('$conductorId', '$this->semana', '$this->ano')";
            if( mysqli_query($this->db, $sql) ) {
                $msj = array("ok"=>true, "mensaje"=>"se inserto el Permiso a la base de datos.");
            } else {
                $msj = array("ok"=>false, "mensaje"=>"no se inserto el conductorID ".$conductorId. " en los permisos de los conductores.");
            }
            return json_encode($msj);
        }

        public function insertarVacaciones($conductorId, $value) {
            $inicio = $value[1].'-'.$value[2].'-'.$value[3];
            $fin = $value[4].'-'.$value[5].'-'.$value[6];
            if($value[7] != '' || $value[7] != null) {
                $incapacidad = 1;
            } else {
                $incapacidad = 0;
            }
            $sql = "INSERT INTO vacaciones_conductores(idConductor, inicio, final, incapacidad, semanaInsertado, anoInsertado) values ('$conductorId', '$inicio', '$fin', '$incapacidad', '$this->semana', '$this->ano')";
            if( mysqli_query($this->db, $sql) ) {
                $msj = array("ok"=>true ,"mensaje"=>"se inserto la Vacacion a la base de datos.");
            } else {
                $msj = array("ok"=>false, "mensaje"=>"no se inserto el conductorID ".$conductorId. " en las Vacaciones.");
            }
            return json_encode($msj);
        }
        
    }
?>
