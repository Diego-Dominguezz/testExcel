<?php


function paginate($reload, $page, $tpages, $adjacents) {
	$prevlabel = "&lsaquo;";
	$nextlabel = "&rsaquo;";
	$out = '<nav aria-label="Pagination"> <ul class="pagination">';

	$mostrar = "document.getElementById('select_lista_errores').value,";
	$ordenar = "document.getElementById('select_order_errores').value";
	$varpag = $mostrar . $ordenar;
	// previous label

	if($page==1) {
		$out.= "<li class='page-item disabled'><span><a class='page-link'>$prevlabel</a></span></li>";
	} else if($page==2) {
		$out.= "<li class='page-item'><span><a class='page-link' href='javascript:void(0);' onclick=\"load(1,".$varpag.")\">$prevlabel</a></span></li>";
	}else {
		$out.= "<li class='page-item'><span><a class='page-link' href='javascript:void(0);' onclick=\"load(".($page-1).",".$varpag.")\">$prevlabel</a></span></li>";

	}

	// first label
	if($page>($adjacents+1)) {
		$out.= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick=\"load(1,".$varpag.")\">1</a></li>";
	}
	// interval
	if($page>($adjacents+2)) {
		$out.= "<li class='page-item'><a class='page-link'>...</a></li>";
	}

	// pages

	$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
	$pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
	for($i=$pmin; $i<=$pmax; $i++) {
		if($i==$page) {
			$out.= "<li class='page-item active'><a class='page-link'>$i</a></li>";
		}else if($i==1) {
			$out.= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick=\"load(1,".$varpag.")\">$i</a></li>";
		}else {
			$out.= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick=\"load(".$i.",".$varpag.")\">$i</a></li>";
		}
	}

	// interval

	if($page<($tpages-$adjacents-1)) {
		$out.= "<li class='page-item'><a class='page-link'>...</a></li>";
	}

	// last

	if($page<($tpages-$adjacents)) {
		$out.= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick=\"load($tpages,".$varpag.")\">$tpages</a></li>";
	}

	// next

	if($page<$tpages) {
		$out.= "<li class='page-item'><span><a class='page-link' href='javascript:void(0);' onclick=\"load(".($page+1).",".$varpag.")\"'>$nextlabel</a></span></li>";
		//$out.= "<li><span><a href=\"javascript:void(0);' onclick='load(".($page+1).",".$varpag.")''>$nextlabel</a></span></li>";
	}else {
		$out.= "<li class='page-item disabled'><span><a class='page-link'>$nextlabel</a></span></li>";
	}

	$out.= "</ul> </nav>";
	return $out;
}
?>
