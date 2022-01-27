<?php
	require "assets/db.php";

	$stmt = $db->query("SELECT P.img, P.titulo, P.descripcion FROM receptor R LEFT OUTER JOIN publicaciones P ON P.id = R.id_publicacion WHERE R.id_rol IN (0,".$_SESSION['rol'].") AND R.activo = 1") or trigger_error($db->error);
	if ($stmt->num_rows > 0) {
		$carousel_item_active = false;
?>

<style type="text/css">
	.picture-container{
		position: relative;
		cursor: pointer;
		text-align: center;
	}
	.picture{
		position: relative;
		justify-content: center;
		width: 120px;
		height: 120px;
		background-color: #999999;
		border: 4px solid #CCCCCC;
		color: #FFFFFF;
		border-radius: 50%;
		overflow: hidden;
		transition: all 0.2s;
		-webkit-transition: all 0.2s;
	}
	.picture:hover{
		border-color: #2ca8ff;
	}
	.picture:hover .edit-label{
		display: block;
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		width: 100%;
		text-align: center;
		opacity: 0.5;
	}
	.picture:hover{
		border-color: #2ca8ff;
	}
	.content.ct-wizard-green .picture:hover{
	    border-color: #05ae0e;
	}
	.content.ct-wizard-blue .picture:hover{
	    border-color: #3472f7;
	}
	.content.ct-wizard-orange .picture:hover{
	    border-color: #ff9500;
	}
	.content.ct-wizard-red .picture:hover{
	    border-color: #ff3b30;
	}
	.picture input[type="file"] {
	    cursor: pointer;
	    display: block;
	    height: 100%;
	    left: 15%;
	    opacity: 0 !important;
	    position: absolute;
	    top: 0;
	    width: 100%;
	}
	.picture-src{
	    width: 100%;
		height: 100%;
	}
	.edit-label{
		background:rgba(0,0,0);
		color: white;
		z-index: 3;
	}
</style>
<div class="container carousel slide" id="container_user" data-ride="carousel">
	<div class="carousel-inner" role="listbox">
<?php
		while ($row = $stmt->fetch_assoc()) {
			if ($carousel_item_active) {
?>
		<div class="carousel-item">
<?php
			}else{
				$carousel_item_active = true;
?>
		<div class="carousel-item active">
<?php
			}
?>

			<img class="d-block w-100" alt="Second slide" src="<?= $row['img'] ?>">
			
		</div>

<?php
		}
?>
		</div>
		
<?php
	}else{
?>
<div class="container" id="container_user">
		<div class="bg">
			<img src="img/main/bgUser.png" id="fondo_user">
		</div>
<?php
	}

	$q = $db->query("SELECT U.img FROM usuarios U WHERE U.idUsuarios=".$_SESSION['logged']) or trigger_error($db->error);
	$row = $q->fetch_assoc();
?>

	<div class="row" style="background-color: white; height:80px; border-radius:6px;">
		<div class="circulo col-lg-1 col-md-1 col-sm-12 picture-container">
			<div class="picture">
				<?php
				if (is_null($row['img']) || !isset($row['img'])) {
					echo '<img class="rounded-circle picture-src" style="object-fit: cover; height: 100%; width: 100%;" src="img/main/userCircle.png" id="icon_user">';
				} else {
					echo '<img class="rounded-circle picture-src" style="object-fit: cover; height: 100%; width: 100%;" src="'.$row['img'].'" id="icon_user">';
				}
				?>
				<input type="file" style="" id="upload-profile-picture" name="upload-profile-picture">
				<a class="edit-label" id="edit-label" href="javascript:void(0)">Editar</a>
			</div>
		</div>
		<div class="userinfo col-lg-11 col-md-11 col-sm-12">
			<h4><?php if (isset($_SESSION['logged'])) { echo $_SESSION['usuario'];}?></h4>
			<h6><?php if (isset($_SESSION['logged'])) { echo $_SESSION['nombre_rol'];}?></h6>
		</div>

<?php

		if(basename($_SERVER["SCRIPT_FILENAME"]) == "index.php" && ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 14 || $_SESSION['rol'] == 8)){
?>
			<div id="regresarPanel" style="position: absolute; bottom: 0px; right: 20px; padding: 10px; cursor: pointer;">
				<i class="fas fa-columns"></i>
				<span class="text-muted" id="cambiarPanel">
				</span>
			</div>
<?php
		}

?>
	</div>
</div>

<script>
	// $(document).ready(function(){
	// 	$('#container_user').carousel({
	// 		interval: 2000
	// 	});
	// });

	// Source
	// https://www.bootply.com/O0Q66tkatA

	var picture = null;

	$(document).ready(function(){
		$('#upload-profile-picture').change(function(){
			readURL(this);
		});
		$('#edit-label').click(function(){
			$('#upload-profile-picture').click();
		});
	});

	function readURL(input){
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function(e){
				uploadProfilePicture(e.target.result);
				$('#icon_user').attr('src', e.target.result).fadeIn('slow');
			}
			reader.readAsDataURL(input.files[0]);
			
		}
	}

	function uploadProfilePicture(img){
		var profilePicture = new FormData();
		
		profilePicture.append('id_user', <?php echo $_SESSION['logged']; ?>);
		profilePicture.append('picture', img);

		$.ajax({
			url 		: 'php/Usuarios/guardarFotoPerfil.php',
			dataType	: 'text',
			type 		: 'POST',
			data 		: profilePicture,
			contentType	: false,
			cache 		: false,
			processData	: false,
			success 	: function(data){
				console.log(data);
				// location.reload();
			},
			error 		: function(e, xhr){
				console.log(e);
				console.log(xhr);
			}
		});
	}
</script>
