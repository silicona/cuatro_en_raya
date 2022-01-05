<?php

require_once 'cuatro.php';

function limpia_varchar($string){

	return filter_var($string, FILTER_SANITIZE_STRING);
}

function isId(int $number){
	return $number > 0;
}

$accion = limpia_varchar($_POST['accion']);

$cuatro = new Cuatro();

if($accion == "juego_automatico"){

	echo json_encode($cuatro -> iniciarJuegoAutomatico());
	exit;
}

if($accion == "jugar_partida"){

	echo json_encode($cuatro -> iniciarPartida());

	exit;
}

if($accion == "echar_ficha"){

	$tablero = $_POST['tablero'];
	$columna = $_POST['columna'];

	echo json_encode($cuatro -> echarFicha($tablero, $columna));
	exit;
}

if($accion == "check_socket"){

	echo json_encode($cuatro -> checkSocket());
	exit;
}

echo json_encode( array(
	'mensaje' => "Acción no reconocida"
) );

exit;
?>
