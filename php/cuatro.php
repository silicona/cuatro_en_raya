<?php

namespace CuatroPhp\php;

// require_once 'config.php';
//require_once 'class.sockeador.php';
//require_once 'class.socketuser.php';

class Cuatro
{
	public $max_tokens;
	public $turno_maq;
	public $tablero;
	public $dificultad;
	// public $triunfos = [
	// 	[0, 1, 2, 3],	[4, 5, 6, 7], [8, 9, 10, 11], [12, 13, 14, 15],
	// 	[0, 4, 8, 12],	[1, 5, 9, 13], [2, 6, 10, 14], [3, 7, 11, 15],
	// 	[0, 5, 10, 15],	[12, 9, 6, 3]
	// ];

	public function __construct()
	{
		//$this->dificultad = 0;
		//$this->max_tokens = 4;
		$this->turno_maq = false;
		$this->tablero = array(
			array(null, null, null, null),
			array(null, null, null, null),
			array(null, null, null, null),
			array(null, null, null, null),
		);

		$this->mem = new Memory();
		$this->brain = new Brain();
	}

	public function echarFicha(array $tablero, int $columna, int $dificultad, string $nombre = '', string $temp_file = '')
	{
		$this->brain->setTablero(array_chunk($tablero, 4));
		$this->turno_maq = false;
		$jugador = 'H';
		$fin_partida = false;
		$linea = false;
		$ganador = false;

		if ($this->brain->anadirTokenAColumna($columna - 1, $jugador) === false) {
			$arr_mensaje = [
				'El máximo número de fichas por columna es 4.',
				'Por favor, elige otra columna.'
			];
		} else {
			$arr_mensaje = ['Has colocado ficha en la columna ' . $columna];
			$this->mem->guardarMovimiento($this->brain->getTableroMerged(), $temp_file);

			if ($this->brain->elegirGanador($jugador)) {
				$fin_partida = true;
				$ganador = $jugador;
				$arr_mensaje[] = 'Has ganado la partida!!';
			} else {
				$this->turno_maq = !$this->turno_maq;
				$jugador = 'M';

				if ($dificultad > 1) $this->brain->setMemory($this->mem->getMemory());

				$calculo = $this->brain->elegirColumnaPorCalculo($jugador, $dificultad);

				if ($this->brain->anadirTokenAColumna($calculo['id_col'], $jugador) !== false) {
					
					if (!$calculo['ok'] && isset($calculo['num_fin'])) {
						$arr_mensaje[] = $calculo['num_fin'] == 1 ? 'No tan rápido, chapa blanda' : 'Evitemos males mayores, simio sin pelo';
					}
					
					$arr_mensaje[] = 'Yo he jugado en la columna ' . ($calculo['id_col'] + 1);

					if ($calculo['ok'] && isset($calculo['num_fin'])) {
						$arr_mensaje[] = 'Gano en  ' . ($calculo['num_fin'] - 1) . ' movimientos, biológico';
					}

					$this->mem->guardarMovimiento($this->brain->getTableroMerged(), $temp_file);

					if ($this->brain->elegirGanador($jugador)) {
						$fin_partida = true;
						$ganador = $jugador;
						$arr_mensaje[] = 'He ganado la partida, biológico!!';
					}
				}
			}
		}

		$tablero = $this->brain->getTableroMerged();
		if ($fin_partida) {
			$linea = $this->brain->getLineaGanadora($tablero, $ganador);

			$saved = $this->mem->guardarPartida($this->mem->getTempPlay($temp_file), $ganador);
			if($saved) $arr_mensaje[] = 'Buena partida, biológico! La recordaré';

			$this->mem->deleteTempPlay($temp_file);
		}

		if (!$fin_partida && !in_array(null, $tablero)) {
			$fin_partida = true;
			$arr_mensaje[] = '';
			$arr_mensaje[] = 'No hay más posiciones disponibles.';
			$arr_mensaje[] = 'La partida termina en tablas';
			$this->mem->deleteTempPlay($temp_file);
		}
		
		//$arr_mensaje[] = 'Memo: ' . gettype($this->brain->getMemory());
		if ($fin_partida && strlen($nombre) > 0) {
			$this->mem->guardarAmistadBender(trim($nombre), $ganador, $dificultad);
		}

		return array(
			'tablero' => $tablero,
			'mensaje' => $arr_mensaje,
			'fin_partida' => $fin_partida,
			'linea' => $linea,
			'temp_file' => $temp_file
		);
	}

	public function echarFichaSocket(array $tablero, int $columna, string $nombre)
	{
		$this->brain->setTablero(array_chunk($tablero, 4));

		$jugador = $this->turno_maq ? "M" : "H";
		// //$this->turno_maq = false;
		$fin_partida = false;
		$linea = false;

		try {
			$token = $this->brain->anadirTokenAColumna($columna - 1, $jugador);
			if ($token === false) {
				$arr_mensaje = [
					'El máximo número de fichas por columna es 4.',
					'Por favor, elige otra columna.'
				];
			} else {
				$arr_mensaje = [$nombre . ' ha colocado ficha en la columna ' . $columna];

				if ($this->brain->elegirGanador($jugador)) {
					$fin_partida = true;
					$arr_mensaje[] = $nombre . ' ha ganado la partida!!';
				}
			}

			$tablero = $this->brain->getTableroMerged();
			// $tablero = array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2], $this->tablero[3]);
			if ($fin_partida) {
				$linea = $this->brain->getLineaGanadora($tablero, $jugador);
				$this->mem->deleteTempPlay($this->temp_file);
				// //Cuatro::guardarPartida($this->getTempPlay(), $this->turno_maq ? 1 : 0);
			}

			if (!$fin_partida && !in_array(null, $tablero)) {
				$fin_partida = true;
				$arr_mensaje[] = 'No hay más posiciones disponibles.';
				$arr_mensaje[] = 'La partida termina en tablas';
				$this->mem->deleteTempPlay($this->temp_file);
			}
		} catch (\Exception $e) {
			$arr_mensaje = ['EcharFicha: ' . $e];
		}

		return array(
			'tablero' => $tablero,
			'mensaje' => $arr_mensaje,
			'fin_partida' => $fin_partida,
			'linea' => $linea,
			'token' => $token
		);
	}

	public function getBenderFriends()
	{
		return $this->mem->getBenderFriends();
	}

	public function getKillsByMemory(): array
	{
		return $this->mem->getKillsByMemory();
	}

	public function iniciarJuegoAutomatico()
	{
		$turno_maq = $this->iniciarTurno();
		$this->brain->setMaxTokens(3);
		$this->brain->setTablero($this->tablero);

		$arr_mensaje = array(
			"Partida automática",
			"Yo llevo las amarillas y tu las verdes.",
			($this->turno_maq ? "El azar ha decidido que empiezo yo." : "El azar quiere que empieces!")
		);
		$ganador = false;
		$token = false;
		for ($i = 0; $i < 12; $i++) {
			$jugador = $turno_maq ? 'M' : 'H';

			$tokenElegido = false;
			while ($tokenElegido === false) {
				$id_col = $this->brain->elegirColumnaAutomatica($jugador);
				$tokenElegido = $this->brain->anadirTokenAColumna($id_col, $jugador);
			}

			if ($this->brain->elegirGanador($jugador)) {
				$ganador = $jugador;
				break;
			}
			$turno_maq = !$turno_maq;
		}

		if ($ganador) {
			if ($ganador == 'H') {
				$arr_mensaje[] = 'Los Hados quisieron que ganarás antes de la última fila, biológico.';
			} else {
				$arr_mensaje[] = 'He ganado antes de la última fila.';
				$arr_mensaje[] = 'La Fortuna lo quiso así.';
			}
		} else {
			$jugador = $turno_maq ? 'M' : 'H';

			$col_ganadora = $this->brain->elegirGanadorAutomatico($jugador);

			if ($col_ganadora !== false) {
				$this->brain->setMaxTokens(4);

				$token = $this->brain->anadirTokenAColumna($col_ganadora, $jugador);
			}

			if ($turno_maq) {
				$arr_mensaje[] = "El próximo movimiento es mio.";
				$arr_mensaje[] = $token ? "Gano jugando en la columna " . ($col_ganadora + 1) : "No puedo ganar con mi próximo movimiento.";
			} else {
				$arr_mensaje[] = "El próximo movimiento es tuyo!";
				$arr_mensaje[] = $token ? "Puedes ganar si juegas en la columna " . ($col_ganadora + 1) : "No puedes ganar con tu próximo movimiento.";
			}
		}

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Jugamos otra vez?";

		return array(
			'tablero' => $this->brain->getTableroMerged(),
			'mensaje' => $arr_mensaje,
			'token' => $token
		);
	}

	public function iniciarJuegoAprendizaje(int $rounds = 1)
	{
		$dificultad = 3;
		$turno_maq = $this->iniciarTurno();
		$this->brain->setTablero($this->tablero);

		$arr_mensaje = array(
			"Partida de aprendizaje - Nivel " . ($dificultad === 3 ? "Sobrio" : "irrelevante"),
			"Yo llevo las amarillas y tu las verdes, que estás aprendiendo, nudillos de hueso.",
			"",
		);

		$human = 0;
		$machine = 0;
		$draws = 0;
		$saved = 0;
		$total = $rounds;
		while ($rounds > 0) {

			$this->brain->setMemory($this->mem->getMemory());
			$this->brain->limpiar_tablero();

			$ganador = false;
			$partida = [];
			for ($i = 0; $i < 16; $i++) {
				$jugador = $turno_maq ? 'M' : 'H';
				$tokenElegido = false;
				while ($tokenElegido === false) {

					$id_col = $jugador == 'M' ? $this->brain->elegirColumna($jugador, $dificultad) : $this->brain->elegirColumnaAprendizaje($jugador);
					$tokenElegido = $this->brain->anadirTokenAColumna($id_col, $jugador);
				}

				$partida[] = $this->brain->getTableroMerged();

				if ($this->brain->elegirGanador($jugador)) {
					$ganador = $jugador;
					break;
				}
				$turno_maq = !$turno_maq;
			}

			if ($ganador !== false) {

				$ganador == 'H' ? $human++ : $machine++;
				$bytes = $this->mem->guardarPartida($partida, $ganador);
				// $bytes = Cuatro::guardarPartida($partida, $ganador == 'M' ? 1 : 2);
				if ($bytes) $saved++;
			} else {
				$draws++;
			}

			$rounds--;
		}

		$arr_mensaje[] = "Ejecutadas $total partidas en solitario.";
		$arr_mensaje[] = "Sir Bender Culo Metálico ha ganado $machine partidas con gran maña.";
		$arr_mensaje[] = "Chapa Blanda ha ganado $human partidas de chiripa.";
		$arr_mensaje[] = "$draws empates insignificantes.";

		$arr_mensaje[] = "** Se han guardado " . $saved . " nuevas partidas **";

		$plays = $this->mem->getMemory();
		$arr_mensaje[] = "** Hay " . count($plays) . " partidas guardadas **";

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Le damos caña otra vez?";

		return array(
			'tablero' => $this->brain->getTableroMerged(),
			'mensaje' => $arr_mensaje,
		);
	}

	public function iniciarJuegoSolitario()
	{
		$dificultad = 3;
		$this->brain->setMemory($this->mem->getMemory());
		$this->brain->setTablero($this->tablero);
		$this->turno_maq = $this->iniciarTurno();

		$arr_mensaje = array(
			"Partida en solitario - Nivel " . ($dificultad === 3 ? "Sobrio" : "irrelevante"),
			"Yo llevo las amarillas y tu las verdes.",
			($this->turno_maq ? "El azar ha decidido que empiezo yo." : "El azar quiere que empieces!")
		);

		$ganador = false;
		$partida = [];
		for ($i = 0; $i < 16; $i++) {
			$jugador = $this->turno_maq ? 'M' : 'H';
			$tokenElegido = false;
			while ($tokenElegido === false) {
				$id_col = $this->brain->elegirColumna($jugador, $dificultad);

				$tokenElegido = $this->brain->anadirTokenAColumna($id_col, $jugador);
			}
			$partida[] = $this->brain->getTableroMerged();

			if ($this->brain->elegirGanador($jugador)) {
				$ganador = $jugador;
				break;
			}
			$this->turno_maq = !$this->turno_maq;
		}

		if ($ganador) {

			$bytes = $this->mem->guardarPartida($partida, $ganador);
			// $bytes = Cuatro::guardarPartida($partida, $ganador == 'M' ? 1 : 2);
			$arr_mensaje[] = $ganador == 'H' ? 'Los Hados quisieron que ganarás, biológico.' : 'He ganado. La Fortuna lo quiso así.';

			$arr_mensaje[] = "Guardados $bytes bytes";
		} else {
			$arr_mensaje[] = "Parece que tenemos tablas";
		}

		$plays = json_decode(file_get_contents(MEM_FILE));
		$arr_mensaje[] = "Hay " . count($plays) . " guardadas";

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Jugamos otra vez?";

		return array(
			'tablero' => $this->brain->getTableroMerged(),
			'mensaje' => $arr_mensaje,
		);
	}

	public function iniciarPartida(int $dificultad)
	{
		$turno_maq = $this->iniciarTurno();
		$this->brain->setTablero($this->tablero);

		$temp_file = 'play_' . (new \DateTime())->format('Uu') . '.txt';

		$arr_mensaje = array(
			"Partida manual",
			"Yo llevo las fichas amarillas y tu las fichas verdes.",
		);

		if ($turno_maq) {
			if ($dificultad > 1) $this->brain->setMemory($this->mem->getMemory());

			$col_maquina = $this->brain->elegirColumna('M', $dificultad);
			$this->brain->anadirTokenAColumna($col_maquina, 'M');

			$arr_mensaje[] = "El azar ha decidido que empiezo yo.";
			$arr_mensaje[] = 'He jugado en la columna ' . ($col_maquina + 1);
			$this->mem->guardarMovimiento($this->brain->getTableroMerged(), $temp_file);
		} else {
			$arr_mensaje[] = "El azar quiere que empieces!";
			$arr_mensaje[] = "Elige una columna";
		}

		return array(
			'tablero' => $this->brain->getTableroMerged(),
			'mensaje' => $arr_mensaje,
			'temp_file' => $temp_file
		);
	}

	public function iniciarTurno(): bool
	{
		return rand() % 2 == 0;
	}
}
