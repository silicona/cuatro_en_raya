<?php

require_once 'cuatro.php';

function limpia_varchar($string){

	return filter_var($string, FILTER_SANITIZE_STRING);
}

$accion = limpia_varchar($_POST['accion']);

$cuatro = new Cuatro();

if($accion == "juego_automatico"){

	echo json_encode( $cuatro -> iniciarJuegoAutomatico());
	exit;

}

echo json_encode( array(
	'mensaje' => "Acción no reconocida"
) );

exit;
?>
