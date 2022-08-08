<?php
require_once 'config.php';
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

if($accion == "juego_solitario"){

	$num_rounds = intVal($_POST['num_rounds']);
	//echo json_encode(['num' => $num_rounds]);
	//exit;
	if(!$num_rounds) $num_rounds = 1;

	echo json_encode($cuatro -> iniciarJuegoAprendizaje($num_rounds));
	// echo json_encode($cuatro -> iniciarJuegoSolitario());
	exit;
}

if($accion == "jugar_partida"){

	//$dificultad = $_POST['dificultad'];
	echo json_encode($cuatro -> iniciarPartida($_POST['dificultad']));
	
	exit;
}

if($accion == "echar_ficha"){
	
	$tablero = $_POST['tablero'];
	$columna = $_POST['columna'];
	$nombre = limpia_varchar($_POST['nombre']);
	$dificultad = $_POST['dificultad'];
	$temp_file = $_POST['temp_file'];
	//$cuatro->temp_file = $temp_file;

	echo json_encode($cuatro -> echarFicha($tablero, $columna, $dificultad, $nombre, $temp_file));
	exit;
}

if($accion == 'get_bender_friends'){
	echo json_encode($cuatro->getBenderFriends());
	exit;
}

if($accion == 'ver_memoria'){
	echo json_encode($cuatro->getMemory());
	exit;
}


echo json_encode( array(
	'mensaje' => "Acción no reconocida"
) );

exit;
?>
