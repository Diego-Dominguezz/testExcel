<?php
session_start();
require_once './assets/vendor/autoload.php';
require './clases/conexion.php';
require './clases/conductores.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$clase_conductores = new Conductores();


    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

    if (file_exists("./formato.xlsx")) $spreadsheet = $reader->load("formato.xlsx");
    else echo "error";

    $d = $spreadsheet->getSheetNames();
    echo count($d) . "<br>";
    $index = 0;
    $resultado = [];
    foreach ($d as $table) {
        echo "--- Tabla " . $table . " --- <br>";
        $sheetData = $spreadsheet->getSheet($index)->toArray();
        $i = 1;
        unset($sheetData[0]);
        $mensaje = [];
        foreach ($sheetData as $t) {
            $driverId = $clase_conductores->formatearDriverId($t[0]);
            $conductorId = $clase_conductores->getConductorId($driverId);
            $idEmpresa = $_POST['idEmpresa'];
            if ($clase_conductores->compararTextos($table, 'FALTAS')) {
                $mensaje[] = $clase_conductores->insertFaltas($driverId, $idEmpresa);
            }
            if ($clase_conductores->compararTextos($table, 'AJUSTES')) {
                $mensaje[] = $clase_conductores->insertAjustes($driverId, $t);
            }
            if ($clase_conductores->compararTextos($table, 'PRESTAMOS')) {
                $mensaje[] = $clase_conductores->insertarPrestamos($driverId, $t);
            }
            if ($clase_conductores->compararTextos($table, 'BONOS')) {
                $mensaje[] = $clase_conductores->insertarBonos($driverId, $t);
            }
            if ($clase_conductores->compararTextos($table, 'PERMISOS')) {
                $mensaje[] = $clase_conductores->insertarPermisos($conductorId, $t);
            }
            if ($clase_conductores->compararTextos($table, 'VACACIONES')) {
                $mensaje[] = $clase_conductores->insertarVacaciones($conductorId, $t);
            }
            $i++;
        }
        $resultado[] = $mensaje;
        $index++;
    }


    return;
if (isset($_POST['submit'])) {
    $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
     
    if(isset($_FILES['file']['name']) && in_array($_FILES['file']['type'], $file_mimes)) {
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);
        if('csv' == $extension) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $spreadsheet = $reader->load($_FILES['file']['tmp_name']);
        if(!file_exists("../uploads/")){
            mkdir("../uploads/", 0777);
        }
        $new_filename = '../uploads/'.$_POST['idEmpresa'] .'-'.time().'-'.$_SESSION['logged'] . '.' . end($temp);
        if(move_uploaded_file($_FILES['file']['tmp_name'], $new_filename)) {
            echo "El archivo es válido y se cargó correctamente.<br>";
            $d=$spreadsheet->getSheetNames();
            echo count($d)."<br>";
            $index = 0;
            $resultado = [];
            // ELIMINAR POR IDEMPRESA; SEMANA Y AÑO
            $clase_conductores->deleteRegistros($_POST['idEmpresa']);
            echo "ELIMINADOS";
            return;
            foreach($d as $table) {
                echo "--- Tabla ".$table." --- <br>";
                $sheetData = $spreadsheet->getSheet($index)->toArray();
                $i=1;
                unset($sheetData[0]);
                $mensaje = [];
                foreach ($sheetData as $t) {
                    $driverId = $clase_conductores->formatearDriverId($t[0]);
                    $conductorId = $clase_conductores->getConductorId($driverId);
                    $idEmpresa = $_POST['idEmpresa'];
                    if( $clase_conductores->compararTextos($table, 'FALTAS') ) {
                        $mensaje[] = $clase_conductores->insertFaltas($driverId, $idEmpresa);
                    }
                    if( $clase_conductores->compararTextos($table, 'AJUSTES') ) {
                        $mensaje[] = $clase_conductores->insertAjustes($driverId, $t);
                    }
                    if( $clase_conductores->compararTextos($table, 'PRESTAMOS') ) {
                        $mensaje[] = $clase_conductores->insertarPrestamos($driverId, $t);
                    }
                    if( $clase_conductores->compararTextos($table, 'BONOS') ) {
                        $mensaje[] = $clase_conductores->insertarBonos($driverId, $t);
                    }
                    if( $clase_conductores->compararTextos($table, 'PERMISOS') ) {
                        $mensaje[] = $clase_conductores->insertarPermisos($conductorId, $t);
                    }
                    if( $clase_conductores->compararTextos($table, 'VACACIONES') ) {
                        $mensaje[] = $clase_conductores->insertarVacaciones($conductorId, $t);
                    }
                    $i++;
                }
                $resultado[] = $mensaje;
                $index++;
            }
            foreach($resultado as $result) {
                foreach($result as $msj) {
                    $decode = json_decode($msj);
                    if(!$decode->ok) {
                        echo $decode->mensaje.'<br>';
                    }
                }
            }
             print_r($resultado);
        } else {
            echo "La subida ha fallado";
        }
    }
}
