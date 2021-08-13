<?php

class Cuatro {

	public $max_tokens;
	public $turno_maq;
	public $tablero;

	public function __construct(){

		$this -> max_tokens = 4;
		$this -> tablero = array(
			array(null,null,null,null),
			array(null,null,null,null),
			array(null,null,null,null),
			array(null,null,null,null),
		);
	}

	public function iniciarJuegoAutomatico(){

		$this -> max_tokens = 3;
		$this -> turno_maq = $this -> iniciarTurno();

		$arr_mensaje = array(
			"Partida automática",
			"Yo llevo las amarillas y tu las verdes.",
			($this -> turno_maq ? "El azar ha decidido que empiezo yo." : "El azar quiere que empieces!")
		);
		
		for($i = 0; $i < 12; $i++){

			$this -> anadirTokenAColumna($this -> elegirColumna());
			$this -> turno_maq = !$this -> turno_maq;
		}

		$col_ganadora = $this -> elegirGanador(false);

		if($this -> turno_maq){

			$arr_mensaje[] = "El próximo movimiento es mio.";

			if($col_ganadora !== false){

				$arr_mensaje[] = "Gano jugando en la columna " . ($col_ganadora + 1);

			} else {

				$arr_mensaje[] = "No puedo ganar con mi próximo movimiento.";
			}

		} else {
			
			$arr_mensaje[] = "El próximo movimiento es tuyo!";

			if($col_ganadora !== false){

				$arr_mensaje[] = "Puedes ganar si juegas en la columna " . ($col_ganadora + 1);

			} else {
				
				$arr_mensaje[] = "No puedes ganar con tu próximo movimiento.";
			}
		}

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Jugamos otra vez?";

		return array(
			'tablero' => array_merge(
				$this -> tablero[0],
				$this -> tablero[1],
				$this -> tablero[2],
				$this -> tablero[3]
			),
			'mensaje' => $arr_mensaje
		);
	}

	public function anadirTokenAColumna($id_columna){

		for($i = 0; $i < $this -> max_tokens; $i++){
			
			if($this -> tablero[$id_columna][$i] == null){

				$this -> tablero[$id_columna][$i] = $this -> turno_maq ? "M" : "H";

				break;
			}
		}
	}

	public function elegirColumna(){

		$arr_cols = [0, 1, 2, 3];

		$col_elegida = $this -> elegirConEstrategia();

		while(!$col_elegida){

			$id = rand(0, count($arr_cols) - 1);

			if($this -> tablero[$arr_cols[$id]][$this -> max_tokens - 1] == null){
				$col_elegida = $arr_cols[$id];
				break;
			}

			array_splice($arr_cols, $id, 1);

			if(empty($arr_cols)){ break; }
		}

		return $col_elegida;
	}

	public function elegirConEstrategia(){

		$jug = $this -> turno_maq ? "M" : "H";

		for($i = 0; $i < 4; $i++){

			if($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == null){
				return $i;
			}
		}

		if($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][1] !== null && $this->tablero[2][2] == null){
			return 2;
		}

		if($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][1] !== null && $this->tablero[1][2] == null){
			return 1;
		}

		return false;
	}

	public function elegirGanador(){

		$jug = $this -> turno_maq ? "M" : "H";

		for($i = 0; $i < 4; $i++){

			if($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == $jug){
				return $i;
			}
		}

		if($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug){
			return 3;
		}

		if($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug){
			return 0;
		}

		return false;
	}

	public function iniciarTurno(){

		return rand() % 2 == 0;
	}
}

?>