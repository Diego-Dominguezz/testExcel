<?php
require(__DIR__ . '/vendor/autoload.php');

use Firebase\JWT\JWT;

$key = base64_encode('KeyFirm123!@#');
$tiempo = time();
$tok = array(
  'iat' => $tiempo, // Tiempo que inició el token
  'exp' => strtotime("+1 hour"), // 1 hora
  'data' => $_SESSION['logged']
);

try {
  $token = JWT::encode($tok, $key);
} catch (Exception $err) {
  echo $err;
}
?>
<script type="text/javascript" src="js/buscadorPrincipal.js"></script>

<header style="position: fixed; z-index: 5; padding: 0; width: 100%; background-color:#212121;" class="headerfix" id="header">

  <div class="container celfix" style="padding-left: 0px !important; padding-right: 0px !important;">
    <nav class="navbar navbar-expand-lg navbar-dark" style="margin:0px !important; padding: 0px !important;">

      <a class="navbar-brand" href="index.php"> <img id="logo" src="img/main/translogo.png" style="width:100% !important; margin-left:16px !important;"> </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="margin: 0 !important; border: none !important; margin-right:16px !important;">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav" style="">
        <div class="form-inline my-2 my-lg-0 ml-auto position-relative">
          <div class="btn my-2 my-sm-0 buscador-button">
            <ion-icon name="search-outline"></ion-icon>
          </div>
          <input class="form-control mr-sm-2 buscador-input" id="buscador-input" type="search" placeholder="Buscar en Transnorte" aria-label="Search" autocomplete="off">
          <div class="position-absolute buscador-resultados-container hidden" id="buscador-resultados-container">
            <ul>
              <?php
              $sqlHeader = $db->query("SELECT Archivo FROM `permisos` WHERE `RolesId` = " . $_SESSION['rol'] . " GROUP BY Archivo") or die($db->error);
              while ($row = $sqlHeader->fetch_assoc()) {
                switch ($row['Archivo']) {
                  case "agregarConductores.php":
                    $nombre = "Agregar Conductores";
                    break;

                  case "agregarViaje.php":
                    $nombre = "Agregar Viaje";
                    break;

                  case "agregarViajesNomina.php":
                    $nombre = "Agregar Viaje Nomina";
                    break;

                  case "archivos.php":
                    $nombre = "Archivos";
                    break;

                  case "buscador.php":
                    $nombre = "Conductores";
                    break;

                  case "choferes.php":
                    $nombre = "Choferes";
                    break;

                  case "ConductoresEditRate.php":
                    $nombre = "Conductores Editar Rate";
                    break;

                  case "editarPedimento.php":
                    $nombre = "Editar Pedimento";
                    break;

                  case "listaViajes.php":
                    $nombre = "Lista Viajes";
                    break;
                  case "listacajas.php":
                    $nombre = "Lista de cajas";
                    break;
                  case "clientes.php":
                    $nombre = "Clientes";
                    break;

                  case "logs.php":
                    $nombre = "Logs";
                    break;

                  case "reciboManual.php":
                    $nombre = "Recibo manual";
                    break;

                  case "nomina.php":
                    $nombre = $idioma['nomina'][$_SESSION['ingles']];
                    break;

                  case "nominaViajes.php":
                    $nombre = "Nomina Viajes";
                    break;

                  case "panelErroresNomina.php":
                    $nombre = "Errores Nomina";
                    break;

                  case "upermisos.php":
                    $nombre = "Permisos";
                    break;

                  case "upload.php":
                    $nombre = "Subir Archivo";
                    break;

                  case "usuarios.php":
                    $nombre = "Usuarios";
                    break;

                  case "verRecibos.php":
                    $nombre = "Recibos";
                    break;

                  case "viajes.php":
                    // $nombre = "Viajes";
                    $nombre = $idioma['viajes'][$_SESSION['ingles']];
                    break;

                  case "ViajesAsignados.php":
                    $nombre = "Viajes Asignados";
                    break;

                  case "ViajesCancelados.php":
                    $nombre = "Viajes Cancelados";
                    break;

                  case "ViajesCompletados.php":
                    $nombre = "Viajes Completados";
                    break;

                  case "ViajesEnCurso.php":
                    $nombre = "Viajes En Curso";
                    break;

                  case "viajesLocales.php":
                    $nombre = "Viajes Locales";
                    break;

                  case "ViajesLocalesAsignados.php":
                    $nombre = "Viajes Locales Asignados";
                    break;

                  case "ViajesLocalesCancelados.php":
                    $nombre = "Viajes Locales Cancelados";
                    break;

                  case "ViajesLocalesCompletados.php":
                    $nombre = "Viajes Locales Completados";
                    break;

                  case "ViajesLocalesEnCurso.php":
                    $nombre = "Viajes Locales En Curso";
                    break;

                  case "ViajesLocalesSolicitados.php":
                    $nombre = "Viajes Locales Solicitados";
                    break;

                  case "ViajesSolicitados.php":
                    $nombre = "Viajes Solicitados";
                    break;

                  case "agregarLocal.php":
                    $nombre = "Agregar Local";
                    break;

                  case "arreglarRecibos.php":
                    $nombre = "Arreglar Recibos";
                    break;

                  case "bannerAdmin.php":
                    $nombre = "Banner Admin";
                    break;

                  case "cajas.php":
                    $nombre = "Cajas";
                    break;

                  case "camiones.php":
                    $nombre = "Camiones";
                    break;

                  case "listaCajas.php":
                    $nombre = "Lista Cajas";
                    break;

                  case "nomClientes.php":
                    $nombre = $idioma['reportes'][$_SESSION['ingles']];
                    break;

                  case "imgConductores.php":
                    $nombre = $idioma['imagenesConductores'][$_SESSION['ingles']];
                    break;

                  case "cambiarSemanaNomina.php":
                    $nombre = $idioma['cambiarSemanaNomina'][$_SESSION['ingles']];
                    break;
                  case "subirArchivo.php":
                    $nombre = $idioma['subirPayroll'][$_SESSION['ingles']];
                    break;
                  case "actualizarRates.php":
                    $nombre = "Actualizar rates";
                    break;
                  case "cambiarImpuestos.php":
                    $nombre = "Cambiar impuestos";
                    break;
                  case "publicarRecibos.php":
                    $nombre = "Publicar recibos";
                    break;
                  case "actualizarDriverid.php":
                    $nombre = "Actualizar DriverID";
                    break;
                  default:
                    $nombre = "Error";
                    break;
                }

                if (permiso($_SESSION['rol'], $row['Archivo']) == true) {
                  echo "<li><a class='w-100 d-block p-1' href='" . $row['Archivo'] . "'>" . ucfirst(strtolower($nombre)) . "</a></li>";
                }
              }

              ?>
              <li id="buscador-no-results">No se encontraron resultados.</li>
            </ul>
          </div>
        </div>
        <ul class="navbar-nav ml-auto" style="float: right;">
          <li class="only_desktop">
            <a style="cursor: pointer; position: relative;">
              <ion-icon id="notificaciones_header" class="header_icon" name="notifications-circle"></ion-icon>
              <span class="badge badge-danger" style="position: absolute; margin-left: -9px; margin-top: -3px; font-size: 70%; user-select: none;" id="notificaciones_contador"></span>
            </a>

            <div id="notificaciones-wrapper">
              <div id="notificaciones-triangulo"></div>
              <div id="notificaciones">
                <div id="notificacion-header">
                  <span>
                    Notificaciones
                  </span>
                </div>
                <div id="notificaciones-contenedor">
                </div>
                <div id="notificacion-footer">
                  <a href="notificaciones.php">
                    Ver todas las notificaciones
                  </a>
                </div>
              </div>
            </div>

          </li>

          <!-- <li class="only_desktop">
            <a href="#"><img src="img/icon/notificacion.png" alt="" class="header_icon"></a>
          </li> -->
          <li class="only_desktop">
            <a href="user.php">
              <ion-icon name="person-circle" size="small" class="header_icon"></ion-icon>
            </a>
          </li>

          <?php if ($_SESSION['rol'] == 1) : ?>
            <li class="only_desktop">
              <a href="localizar.php?p=<?= $token ?>&all">
                <ion-icon name="navigate-circle" class="header_icon"></ion-icon>
              </a>
            </li>
          <?php endif; ?>

          <li class="only_desktop">
            <a href="assets/logout.php">
              <ion-icon name="log-out" class="header_icon"></ion-icon>
            </a>
          </li>

          <?php if (permiso($_SESSION['rol'], "nomina.php") == true) : ?>
            <li class="only_mobile">
              <a href="nomina.php">
                Nomina
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "buscador.php") == true) : ?>
            <li class="only_mobile">
              <a href="buscador.php">
                Conductores
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "viajes.php") == true) : ?>
            <li class="only_mobile">
              <a href="viajes.php">
                Cruces
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "viajesLocales.php") == true) : ?>
            <li class="only_mobile">
              <a href="viajesLocales.php">
                Locales
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "usuarios.php") == true) : ?>
            <li class="only_mobile">
              <a href="usuarios.php">
                Usuarios
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "camiones.php") == true) : ?>
            <li class="only_mobile">
              <a href="camiones.php">
                Camiones
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "clientes.php") == true) : ?>
            <li class="only_mobile">
              <a href="clientes.php">
                Clientes
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "verRecibos.php") == true) : ?>
            <li class="only_mobile">
              <a href="verRecibos.php">
                Recibos
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "bannerAdmin.php") == true) : ?>
            <li class="only_mobile">
              <a href="bannerAdmin.php">
                Avisos
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "cajas.php") == true) : ?>
            <li class="only_mobile">
              <a href="cajas.php">
                Cajas
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "cajas.php") == true) : ?>
            <li class="only_mobile">
              <a href="cajas.php">
                Driver Trips
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "agregarViajesNomina.php") == true) : ?>
            <li class="only_mobile">
              <a href="agregarViajesNomina.php">
                Agregar Viajes Nomina
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "nomClientes.php") == true) : ?>
            <li class="only_mobile">
              <a href="nomClientes.php">
                Reports
              </a>
            </li>
          <?php endif ?>
          <?php if (permiso($_SESSION['rol'], "imgConductores.php") == true) : ?>
            <li class="only_mobile">
              <a href="imgConductores.php">
                Imagenes Conductores
              </a>
            </li>
          <?php endif ?>
          <li class="only_mobile">
            <a href="assets/logout.php">Cerrar Sesión</a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</header>

<?php include "assets/footer.php"; ?>