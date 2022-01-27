<?php
$url = explode('/',$_SERVER['REQUEST_URI']);
if(!isset($idioma)){
  require("assets/idioma.php");
}
?>
  <?php if($_SESSION['rol'] == 5 || $_SESSION['rol'] == 7){} else { ?>
  <div class="lateral">
    <?php if (permiso($_SESSION['rol'], "nomina.php") == true) : ?>
    <a href="nomina.php" style="border-radius:6px;">
      <div class="item <?php if($url[2] == "nomina.php") { echo "active"; } ?>" id="0">
        <?= $idioma['nomina'][$_SESSION['ingles']] ?>
      </div>
    </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "buscador.php") == true) : ?>
    <a href="buscador.php">
  <div class="item <?php if($url[2] == "buscador.php") { echo "active"; } ?>" id="1">
    Conductores
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "viajes.php") == true) : ?>
    <a href="viajes.php">
      <div class="item <?php if($url[2] == "viajes.php") { echo "active"; } ?>" id="2">
      Cruces
    </div>
    </a>
    <?php endif ?>

    <?php if (permiso($_SESSION['rol'], "actualizarDriverid.php")) : ?>
    <a href="actualizarDriverid.php">
      <div class="item <?php if($url[2] == "actualizarDriverid.php") { echo "active"; } ?>" id="2">
      Cambiar DriverId
    </div>
    </a>
    <?php endif ?>

    <?php if (permiso($_SESSION['rol'], "viajesLocales.php") == true) : ?>
    <a href="viajesLocales.php">
      <div class="item <?php if($url[2] == "viajesLocales.php") { echo "active"; } ?>" id="5">
        Locales
      </div>
    </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "usuarios.php") == true) : ?>
    <a href="usuarios.php">
  <div class="item <?php if($url[2] == "usuarios.php") { echo "active"; } ?>" id="3">
    Usuarios
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "reportes.php") == true) : ?>
    <a href="reportes.php">
  <div class="item <?php if($url[2] == "reportes.php") { echo "active"; } ?>" id="6">
    Reportes
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "camiones.php") == true) : ?>
    <a href="camiones.php">
  <div class="item <?php if($url[2] == "camiones.php") { echo "active"; } ?>" id="7">
    Camiones
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "clientes.php") == true) : ?>
      <a href="clientes.php">
        <div class="item <?php if($url[2] == "clientes.php") { echo "active"; } ?>" id="7">
          Clientes
        </div>
    </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "verRecibos.php") == true) : ?>
    <a href="verRecibos.php">
  <div class="item <?php if($url[2] == "verRecibos.php") { echo "active"; } ?>" id="8">
    Recibos
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "bannerAdmin.php") == true) : ?>
    <a href="bannerAdmin.php">
  <div class="item <?php if($url[2] == "bannerAdmin.php") { echo "active"; } ?>" id="9">
    Avisos
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "cajas.php") == true) : ?>
    <a href="cajas.php">
  <div class="item <?php if($url[2] == "cajas.php") { echo "active"; } ?>" id="10">
    Cajas
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "nominasDisponibles.php") == true) : ?>
    <a href="nominasDisponibles.php">
  <div class="item <?php if($url[2] == "nominasDisponibles.php") { echo "active"; } ?>" id="11">
    Calculos disponibles
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "nomClientes.php") == true) : ?>
    <a href="nomClientes.php">
  <div class="item <?php if($url[2] == "nomClientes.php") { echo "active"; } ?>" id="12">
    Reports
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "imgConductores.php") == true) : ?>
    <a href="imgConductores.php">
  <div class="item <?php if($url[2] == "imgConductores.php") { echo "active"; } ?>" id="12">
    <?= $idioma['imagenesConductores'][$_SESSION['ingles']];4 ?>
  </div>
  </a>
    <?php endif ?>

    <?php if (permiso($_SESSION['rol'], "insertarCuentaElectronica.php") == true) : ?>
    <a href="insertarCuentaElectronica.php">
  <div class="item <?php if($url[2] == "insertarCuentaElectronica.php") { echo "active"; } ?>" id="12">
    <? //$idioma['imagenesConductores'][$_SESSION['ingles']];4 ?>
    Insertar Cuenta Electronica
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "subirArchivo.php") && !($_SESSION['cliente'] != 15 && $_SESSION['cliente'] != 9 && $_SESSION['cliente'] != 50 && $_SESSION['cliente'] != 1)) : ?>
    <a href="subirArchivo.php">
  <div class="item <?php if($url[2] == "subirArchivo.php") { echo "active"; } ?>" id="12">
    <? //$idioma['imagenesConductores'][$_SESSION['ingles']];4 ?>
    Subir Excel
  </div>
  </a>
    <?php endif ?>
    <?php if (permiso($_SESSION['rol'], "crearSello.php") == true) : ?>
    <a href="crearSello.php">
      <div class="item <?php if($url[2] == "crearSello.php") { echo "active"; } ?>" id="5">
        Crear documento oficial
      </div>
    </a>
    <?php endif ?>
  </div>
  

  <?php } ?>
