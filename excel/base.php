<?php
/**
 * XLS parsing uses php-excel-reader from http://code.google.com/p/php-excel-reader/
 */

 set_time_limit(0);
	require('php-excel-reader/excel_reader2.php');
	require('SpreadsheetReader.php');

  require_once "../assets/db.php";
	try
	{
		$Spreadsheet = new SpreadsheetReader("base.xlsx");

		$Sheets = $Spreadsheet -> Sheets();


			$Spreadsheet -> ChangeSheet(0);
      $i = 0;
			foreach ($Spreadsheet as $Key => $Row){
        $i++;
				if ($Row){
          if($Row[0]==""){
            break;
          }
          if($Key == 0){
            continue;
          }

          $choferNombre = explode(" ",$Row[2]);
          $choferFix = array();

          foreach ($choferNombre as $key => $value) {
            if($value){
              $choferFix[] = $value;
            }
          }




          switch (count($choferFix)) {
            case 3:
              $ApellidoPaterno = $choferFix[0];
              $ApellidoMaterno = $choferFix[1];
              $Nombre = $choferFix[2];
              break;
            case 4:
            $ApellidoPaterno = $choferFix[0];
            $ApellidoMaterno = $choferFix[1];
            $Nombre = $choferFix[2]." ".$choferFix[3];
              break;
            default:
              echo "<br>".$i."<br>";
              $ApellidoPaterno = "";
              $ApellidoMaterno = "";
              $Nombre = "";
              echo print_r($choferFix);
              break;
          }
          $Direccion = mysqli_real_escape_string($db, $Row[5]);
          $Ciudad = mysqli_real_escape_string($db, $Row[6]);
          $Estado = mysqli_real_escape_string($db, $Row[7]);
          switch (trim(strtoupper($Row[3]))) {
            case 'ACTIVO':
              $Status = 1;
              break;
            case 'BAJA':
              $Status = 0;
              break;
            case 'REINGRESO':
              $Status = 2;
              break;
            case 'SUSPENSION':
              $Status = 3;
              break;
          }
          $fechaAlta = strtotime($Row[4]);
          $Compania = $Row[0];
          $TelefonoFijo = mysqli_real_escape_string($db, $Row[8]);
          $TelefonoMovil = mysqli_real_escape_string($db, $Row[9]);
          $TelefonoAmericano = mysqli_real_escape_string($db, $Row[10]);
          $DriverId = mysqli_real_escape_string($db, $Row[1]);
          //echo $i."<br>";
          mysqli_query($db, "INSERT INTO conductores (DriverId, Nombre, ApellidoPaterno, ApellidoMaterno, Direccion, Ciudad, Estado, Status, fechaAlta, Compania, TelefonoFijo, TelefonoMovil, TelefonoAmericano) VALUES (
          \"$DriverId\", \"$Nombre\", \"$ApellidoPaterno\", \"$ApellidoMaterno\", \"$Direccion\", \"$Ciudad\", \"$Estado\", \"$Status\", \"$fechaAlta\", \"$Compania\", \"$TelefonoFijo\", \"$TelefonoMovil\", \"$TelefonoAmericano\")")or die(mysqli_error($db));

				}
				else{
					var_dump($Row);
				}
			}
	}
	catch (Exception $E)
	{
		echo $E -> getMessage();
	}
?>
