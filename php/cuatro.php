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
	public $triunfos = [
		[0, 1, 2, 3],	[4, 5, 6, 7], [8, 9, 10, 11], [12, 13, 14, 15],
		[0, 4, 8, 12],	[1, 5, 9, 13], [2, 6, 10, 14], [3, 7, 11, 15],
		[0, 5, 10, 15],	[12, 9, 6, 3]
	];

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
		//$this->tablero = array_chunk($tablero, 4);
		$this->brain->setTablero(array_chunk($tablero, 4));

		//$this->dificultad = $dificultad;
		//$this->temp_file = $temp_file;
		$this->turno_maq = false;
		$jugador = 'H';
		$fin_partida = false;
		$linea = false;
		$ganador = '';

		try {
			$token = $this->brain->anadirTokenAColumna($columna - 1, $jugador);
			// $token = $this->anadirTokenAColumna($columna - 1);
			if ($token === false) {
				$arr_mensaje = [
					'El máximo número de fichas por columna es 4.',
					'Por favor, elige otra columna.'
				];
			} else {
				$arr_mensaje = ['Has colocado ficha en la columna ' . $columna];
				$this->mem->guardarMovimiento($this->brain->getTableroMerged(), $temp_file);
				// $this->guardarMovimiento();

				if ($this->brain->elegirGanador($jugador)) {
					$fin_partida = true;
					$ganador = $jugador;
					$arr_mensaje[] = 'Has ganado la partida!!';
				} else {
					$this->turno_maq = !$this->turno_maq;
					$jugador = 'M';

					$col_maquina = $this->brain->elegirColumna($jugador, $dificultad);
					if ($this->brain->anadirTokenAColumna($col_maquina, $jugador) !== false) {
						$arr_mensaje[] = 'Yo he jugado en la columna ' . ($col_maquina + 1);
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
			// $tablero = array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2], $this->tablero[3]);
			if ($fin_partida) {
				$linea = $this->brain->getLineaGanadora($tablero, $ganador);

				$this->mem->guardarPartida($this->mem->getTempPlay($temp_file), $ganador == 'M' ? 1 : 0);
				$this->mem->deleteTempPlay($temp_file);
				// Cuatro::guardarPartida($this->getTempPlay(), $this->turno_maq ? 1 : 0);
				// $this->deleteTempPlay();
				// Cuatro::guardarPartida([], $this->turno_maq ? 1 : 0);
			}

			if (!$fin_partida && !in_array(null, $tablero)) {
				$fin_partida = true;
				$arr_mensaje[] = 'No hay más posiciones disponibles.';
				$arr_mensaje[] = 'La partida termina en tablas';
				$this->mem->deleteTempPlay($temp_file);
			}

			if ($fin_partida && strlen($nombre) > 0) {
				$this->mem->guardarAmistadBender(trim($nombre), $ganador, $dificultad);
			}
		} catch (\Exception $e) {
			$arr_mensaje = ['EcharFicha: ' . $e];
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
		// $this->tablero = array_chunk($tablero, 4);
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

	// private function elegirColumnaAprendizaje(string $jug = 'H')
	// {
	// 	$exceps = $this->getLosesByMemory($jug);

	// 	// $id_col_trampa = $this->elegirConTrampa($jug == 'H' ? 'M' : 'H');
	// 	// if ($id_col_trampa !== false && !in_array($id_col_trampa, $exceps)) $exceps[] = $id_col_trampa;

	// 	$id_col_a_evitar = $this->brain->getEstrategiaFiestero($jug == 'H' ? 'M' : 'H');

	// 	if ($id_col_a_evitar !== false && !in_array($id_col_a_evitar, $exceps)) {
	// 		$exceps[] = $id_col_a_evitar;
	// 		sort($exceps);
	// 	}

	// 	$col_elegida = $this->brain->elegirColumnaAleatoria($exceps);

	// 	return $col_elegida;
	// }

	public function getBenderFriends()
	{
		return $this->mem->getBenderFriends();
	}

	// private function getEstrategiaResacoso(string $jug)
	// {
	// 	$nexts = $this->getNextsByMemory($jug);

	// 	if (count($nexts) > 0) {

	// 		$cols = $this->dificultad == 3 ? array_shift($nexts) : array_pop($nexts);

	// 		$id_col = $cols[array_rand($cols)];
	// 	} else {
	// 		$id_col = $this->brain->getEstrategiaFiestero($jug);
	// 	}

	// 	return $id_col;
	// }

	// private function getLosesByMemory(string $jug = 'H')
	// {
	// 	$exceps = [];

	// 	$plays = $this->memory;
	// 	// $plays = json_decode(@file_get_contents(MEM_FILE));
	// 	if ($plays) {

	// 		$contra = $jug == 'H' ? 'M' : 'H';
	// 		$tablero = array_map(function ($cell) use ($jug, $contra) {
	// 			if ($cell == $contra) $cell = 1;
	// 			else if ($cell == $jug) $cell = 2;
	// 			else $cell = '';
	// 			return $cell;
	// 		}, array_merge(...$this->tablero));

	// 		foreach ($plays as $play) {
	// 			$filt = array_filter($play[0]);
	// 			$inicio = array_shift($filt) == 1 ? true : false;
	// 			foreach ($play as $i => $move) {

	// 				// Saltamos movimientos del ganador
	// 				if ($inicio && $i % 2 != 0) continue;
	// 				else if (!$inicio && $i % 2 == 0) continue;

	// 				if ($tablero == $move) {
	// 					$id_next = $i + 1; // Proximo movimiento registrado de jug
	// 					$id_next_op = $i + 2; // Futuro movimiento de contra
	// 					$id_col_aux = false;
	// 					foreach ($play[$id_next] as $id_token => $next) {
	// 						if ($tablero[$id_token] == '' && $next == 2) {
	// 							$id_col_aux = $this->brain->determinarColumna($id_token);
	// 							break;
	// 						}
	// 					}

	// 					if ($id_col_aux !== false) {
	// 						if (in_array($id_col_aux, $exceps) === false) $exceps[] = $id_col_aux;
	// 					}
	// 					break;
	// 				}
	// 			}
	// 		}

	// 		if (count($exceps) > 0) sort($exceps);
	// 	}

	// 	return $exceps;
	// }

	public function getKillsByMemory(): array
	{
		return $this->mem->getKillsByMemory();
	}

	// private function getNextsByMemory(string $jug)
	// {
	// 	$nexts = [];
	// 	$plays = $this->memory;
	// 	// $plays = json_decode(@file_get_contents(MEM_FILE));

	// 	if ($plays) {
	// 		$contra = $jug == 'M' ? 'H' : 'M';
	// 		$tablero = array_map(function ($cell) use ($jug, $contra) {
	// 			if ($cell == $contra) $cell = 2;
	// 			else if ($cell == $jug) $cell = 1;
	// 			else $cell = '';
	// 			return $cell;
	// 		}, array_merge(...$this->tablero));

	// 		foreach ($plays as $play) {
	// 			$filt = array_filter($play[0]);
	// 			$inicio = array_shift($filt) == 1 ? true : false;
	// 			foreach ($play as $i => $move) {

	// 				// Saltamos movimientos del oponente
	// 				if ($inicio && $i % 2 == 0) continue;
	// 				else if (!$inicio && $i % 2 != 0) continue;

	// 				if ($tablero == $move) {
	// 					$id_next = $i + 1; // Proximo movimiento registrado de jug
	// 					$id_next_op = $i + 2; // Futuro movimiento de contra
	// 					$id_col_aux = false;
	// 					foreach ($play[$id_next] as $id_token => $next) {
	// 						if ($tablero[$id_token] == '' && $next == 1) {
	// 							$id_col_aux = $this->brain->determinarColumna($id_token);
	// 							break;
	// 						}
	// 					}

	// 					if ($id_col_aux !== false) {
	// 						$stepsVictory = count($play) - $id_next;

	// 						if (!isset($nexts[$stepsVictory]))	$nexts[$stepsVictory] = [];

	// 						if (!in_array($id_col_aux, $nexts[$stepsVictory]))
	// 							$nexts[$stepsVictory][] = $id_col_aux;
	// 					}
	// 					break;
	// 				}
	// 			}
	// 		}

	// 		if (count($nexts) > 0) ksort($nexts);
	// 	}

	// 	return $nexts;
	// }

	public function iniciarJuegoAutomatico()
	{
		//$this->max_tokens = 3;
		$this->brain->setMaxTokens(3);
		$this->turno_maq = $this->iniciarTurno();
		$this->brain->setTablero($this->tablero);

		$arr_mensaje = array(
			"Partida automática",
			"Yo llevo las amarillas y tu las verdes.",
			($this->turno_maq ? "El azar ha decidido que empiezo yo." : "El azar quiere que empieces!")
		);
		$ganador = false;
		$token = false;
		for ($i = 0; $i < 12; $i++) {
			$jugador = $this->turno_maq ? 'M' : 'H';

			$tokenElegido = false;
			while ($tokenElegido === false) {
				$id_col = $this->brain->getEstrategiaAutomatica($jugador);
				$tokenElegido = $this->brain->anadirTokenAColumna($id_col, $jugador);
			}

			if ($this->brain->elegirGanador($jugador)) {
				$ganador = $jugador;
				break;
			}
			$this->turno_maq = !$this->turno_maq;
		}

		if ($ganador) {
			if ($ganador == 'H') {
				$arr_mensaje[] = 'Los Hados quisieron que ganarás antes de la última fila, biológico.';
			} else {
				$arr_mensaje[] = 'He ganado antes de la última fila.';
				$arr_mensaje[] = 'La Fortuna lo quiso así.';
			}
		} else {
			$jugador = $this->turno_maq ? 'M' : 'H';

			$col_ganadora = $this->brain->elegirGanadorAutomatico($jugador);

			if ($col_ganadora !== false) {
				//$this->max_tokens = 4;
				$this->brain->setMaxTokens(4);

				$token = $this->brain->anadirTokenAColumna($col_ganadora, $jugador);
			}

			if ($this->turno_maq) {
				$arr_mensaje[] = "El próximo movimiento es mio.";
				$arr_mensaje[] = $token ? "Gano jugando en la columna " . ($col_ganadora + 1) : "No puedo ganar con mi próximo movimiento.";
				// $arr_mensaje[] = ($col_ganadora !== false) ? "Gano jugando en la columna " . ($col_ganadora + 1) : "No puedo ganar con mi próximo movimiento.";
				// if ($col_ganadora !== false) {
				// 	$arr_mensaje[] = "Gano jugando en la columna " . ($col_ganadora + 1);
				// } else {
				// 	$arr_mensaje[] = "No puedo ganar con mi próximo movimiento.";
				// }
			} else {
				$arr_mensaje[] = "El próximo movimiento es tuyo!";
				$arr_mensaje[] = $token ? "Puedes ganar si juegas en la columna " . ($col_ganadora + 1) : "No puedes ganar con tu próximo movimiento.";
				// if ($col_ganadora !== false) {
				// 	$arr_mensaje[] = "Puedes ganar si juegas en la columna " . ($col_ganadora + 1);
				// } else {
				// 	$arr_mensaje[] = "No puedes ganar con tu próximo movimiento.";
				// }
			}
		}

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Jugamos otra vez?";

		return array(
			'tablero' => $this->brain->getTableroMerged(),
			// 'tablero' => array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
			'token' => $token
		);
	}

	public function iniciarJuegoAprendizaje(int $rounds = 1)
	{
		//$this->max_tokens = 4;
		$dificultad = 3;
		$this->turno_maq = $this->iniciarTurno();
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
			//$this->memory = $this->mem->getMemory();
			// $this->memory = json_decode(@file_get_contents(MEM_FILE));
			$this->brain->limpiar_tablero();

			$ganador = false;
			$partida = [];
			for ($i = 0; $i < 16; $i++) {
				$jugador = $this->turno_maq ? 'M' : 'H';
				$tokenElegido = false;
				while ($tokenElegido === false) {
					// if ($this->turno_maq) {
					// 	$id_col = $this->brain->elegirColumna();
					// } else {
					// 	$id_col = $this->elegirColumnaAprendizaje();
					// }
					$id_col = $jugador == 'M' ? $this->brain->elegirColumna($jugador, $dificultad) : $this->brain->elegirColumnaAprendizaje($jugador);
					$tokenElegido = $this->brain->anadirTokenAColumna($id_col, $jugador);
				}

				$partida[] = $this->brain->getTableroMerged();
				// $partida[] = array_merge(...$this->tablero);

				if ($this->brain->elegirGanador($jugador)) {
					$ganador = $jugador;
					// $ganador = $this->turno_maq ? 'M' : 'H';
					break;
				}
				$this->turno_maq = !$this->turno_maq;
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
		// $plays = json_decode(file_get_contents(MEM_FILE));
		$arr_mensaje[] = "** Hay " . count($plays) . " partidas guardadas **";

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Le damos caña otra vez?";

		return array(
			'tablero' => $this->brain->getTableroMerged(),
			// 'tablero' => array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
		);
	}

	public function iniciarJuegoSolitario()
	{
		//$this->max_tokens = 4;
		$dificultad = 3;
		$this->brain->setMemory($this->mem->getMemory());
		$this->brain->setTablero($this->tablero);
		$this->turno_maq = $this->iniciarTurno();

		$arr_mensaje = array(
			// "Partida en solitario - Nivel Borracho",
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
			// $partida[] = array_merge(...$this->tablero);

			if ($this->brain->elegirGanador($jugador)) {
				$ganador = $jugador;
				break;
			}
			$this->turno_maq = !$this->turno_maq;
		}

		if ($ganador) {

			// if ($ganador == 'H') {
			// 	$arr_mensaje[] = 'Los Hados quisieron que ganarás, biológico.';
			// } else {
			// 	$arr_mensaje[] = 'He ganado. La Fortuna lo quiso así.';
			// }

			$bytes = $this->mem->guardarPartida($partida, $ganador);
			$arr_mensaje[] = $ganador == 'H' ? 'Los Hados quisieron que ganarás, biológico.' : 'He ganado. La Fortuna lo quiso así.';
			// $bytes = Cuatro::guardarPartida($partida, $ganador == 'M' ? 1 : 2);

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
			// 'tablero' => array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
		);
	}

	public function iniciarPartida(int $dificultad)
	{
		//$this->max_tokens = 4;
		$this->turno_maq = $this->iniciarTurno();
		$this->brain->setTablero($this->tablero);

		//$this->dificultad = $dificultad;
		$temp_file = 'play_' . (new \DateTime())->format('Uu') . '.txt';

		$arr_mensaje = array(
			"Partida manual",
			"Yo llevo las fichas amarillas y tu las fichas verdes.",
		);

		if ($this->turno_maq) {
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
			// 'tablero' => array_merge($this->tablero[0],	$this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
			'temp_file' => $temp_file
		);
	}

	public function iniciarTurno(): bool
	{
		return rand() % 2 == 0;
	}
}
