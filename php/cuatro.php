<?php
// require_once 'config.php';
require_once 'class.sockeador.php';
require_once 'class.socketuser.php';

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
		$this->dificultad = 0;
		$this->max_tokens = 4;
		$this->turno_maq = false;
		$this->tablero = array(
			array(null, null, null, null),
			array(null, null, null, null),
			array(null, null, null, null),
			array(null, null, null, null),
		);
	}

	private function anadirTokenAColumna($id_columna)
	{
		for ($i = 0; $i < $this->max_tokens; $i++) {

			if ($this->tablero[$id_columna][$i] == null) {

				$this->tablero[$id_columna][$i] = $this->turno_maq ? "M" : "H";

				return $this->determinarToken($id_columna, $i);
			}
		}

		return false;
	}

	private function deleteTempPlay()
	{
		return unlink(BASE_TEMP . $this->temp_file);
	}

	private function determinarColumna(int $id_token): int
	{
		return ceil(++$id_token / 4) - 1;
	}

	private function determinarToken(int $id_columna, int $altura): int
	{
		$posiciones = array_chunk(range(0, 15), 4);
		return $posiciones[$id_columna][$altura];
	}

	public function echarFicha(array $tablero, int $columna, int $dificultad, string $nombre = '', string $temp_file = '')
	{
		$this->tablero = array_chunk($tablero, 4);
		$this->dificultad = $dificultad;
		$this->temp_file = $temp_file;
		$this->turno_maq = false;
		$fin_partida = false;
		$linea = false;
		$ganador = '';

		try {
			$token = $this->anadirTokenAColumna($columna - 1);
			if ($token === false) {
				$arr_mensaje = [
					'El máximo número de fichas por columna es 4.',
					'Por favor, elige otra columna.'
				];
			} else {
				$arr_mensaje = ['Has colocado ficha en la columna ' . $columna];
				$this->guardarMovimiento();

				if ($this->elegirGanador()) {
					$fin_partida = true;
					$ganador = 'H';
					$arr_mensaje[] = 'Has ganado la partida!!';
				} else {
					$this->turno_maq = !$this->turno_maq;

					$col_maquina = $this->elegirColumna();
					if ($this->anadirTokenAColumna($col_maquina) !== false) {
						$arr_mensaje[] = 'Yo he jugado en la columna ' . ($col_maquina + 1);
						$this->guardarMovimiento();

						if ($this->elegirGanador()) {
							$fin_partida = true;
							$ganador = 'M';
							$arr_mensaje[] = 'He ganado la partida, biológico!!';
						}
					}
				}
			}

			$tablero = array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2], $this->tablero[3]);
			if ($fin_partida) {
				$linea = $this->getLineaGanadora($tablero);

				Cuatro::guardarPartida($this->getTempPlay(), $this->turno_maq ? 1 : 0);
				$this->deleteTempPlay();
				// Cuatro::guardarPartida([], $this->turno_maq ? 1 : 0);
			}

			if (!$fin_partida && !in_array(null, $tablero)) {
				$fin_partida = true;
				$arr_mensaje[] = 'No hay más posiciones disponibles.';
				$arr_mensaje[] = 'La partida termina en tablas';
				$this->deleteTempPlay();
			}

			if ($fin_partida && strlen($nombre) > 0) {
				$this->guardarAmistadBender(trim($nombre), $ganador);
			}
		} catch (Exception $e) {
			$arr_mensaje = ['EcharFicha: ' . $e];
		}

		return array(
			'tablero' => $tablero,
			'mensaje' => $arr_mensaje,
			'fin_partida' => $fin_partida,
			'linea' => $linea,
			'temp_file' => $this->temp_file
		);
	}

	public function echarFichaSocket(array $tablero, int $columna, string $nombre)
	{
		$this->tablero = array_chunk($tablero, 4);
		//$this->turno_maq = false;
		$fin_partida = false;
		$linea = false;

		try {
			$token = $this->anadirTokenAColumna($columna - 1);
			if ($token === false) {
				$arr_mensaje = [
					'El máximo número de fichas por columna es 4.',
					'Por favor, elige otra columna.'
				];
			} else {
				$arr_mensaje = [$nombre . ' ha colocado ficha en la columna ' . $columna];

				if ($this->elegirGanador()) {
					$fin_partida = true;
					$arr_mensaje[] = $nombre . ' ha ganado la partida!!';
				}
			}

			$tablero = array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2], $this->tablero[3]);
			if ($fin_partida) {
				$linea = $this->getLineaGanadora($tablero);
				//Cuatro::guardarPartida($this->getTempPlay(), $this->turno_maq ? 1 : 0);
				$this->deleteTempPlay();
			}

			if (!$fin_partida && !in_array(null, $tablero)) {
				$fin_partida = true;
				$arr_mensaje[] = 'No hay más posiciones disponibles.';
				$arr_mensaje[] = 'La partida termina en tablas';
				$this->deleteTempPlay();
			}
		} catch (Exception $e) {
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

	private function elegirColumna()
	{

		$col_elegida = $this->elegirConEstrategia();
		//$col_muerte_subita = $this->elegirConTrampa('H');
		
		//if($col_trampa !== false && $col_elegida == $col_trampa) $col_elegida = false;
		//if($col_muerte_subita !== false && $col_elegida == $col_muerte_subita) $col_elegida = false;
		
		if($col_elegida === false) {
			$exceps = [];
			$col_trampa = $this->elegirConTrampa();
			if($col_trampa !== false) $exceps[] = $col_trampa;

			$col_elegida = $this->elegirColumnaAleatoria($exceps);
		}
		return $col_elegida;
	}

	private function elegirColumnaAleatoria(array $exceps = [])
	{
		if(count($exceps) > 3) $exceps = [];

		$arr_cols = array_diff([0, 1, 2, 3], $exceps);
		sort($arr_cols);

		$id_col = false;
		while ($id_col === false) {

			$index = rand(0, count($arr_cols) - 1);
			$aux_id = $arr_cols[$index];

			if (!preg_match('/(M|H)/', strval($this->tablero[$aux_id][$this->max_tokens - 1]))) {
				$id_col = $aux_id;
				break;
			}

			array_splice($arr_cols, $index, 1);

			if (empty($arr_cols)) {

				if(!empty($exceps)) {
					$arr_cols = $exceps;
					$exceps = [];
				} else {
					break;
				}
			}
		}

		return $id_col;
	}


	private function elegirColumnaAprendizaje(string $jug = 'H')
	{
		$id_col_trampa = $this->elegirConTrampa($jug == 'H' ? 'M' : 'H');
		if($id_col_trampa !== false) return $id_col_trampa;

		$exceps = $this->getLosesByMemory($jug);

		$id_col_a_evitar = $this->getEstrategiaFiestero($jug == 'H' ? 'M' : 'H');

		if($id_col_a_evitar !== false && !in_array($id_col_a_evitar, $exceps)) {
			$exceps[] = $id_col_a_evitar;
			sort($exceps);
		}

		$col_elegida = $this->elegirColumnaAleatoria($exceps);

		return $col_elegida;
	}

	private function elegirConEstrategia()
	{
		$jugador = $this->turno_maq ? "M" : "H";
		switch ($this->dificultad) {
			case 1:
				$fn_estrategia = 'getEstrategiaFiestero';
				break;
			case 2:
			case 3:
				$fn_estrategia = 'getEstrategiaResacoso';
				break;
			default:
				$fn_estrategia = 'getEstrategiaBorracho';
		}

		$id_col = $this->$fn_estrategia($jugador);
		if ($id_col !== false) return $id_col;

		if ($this->max_tokens == 4) return $this->$fn_estrategia($jugador == 'M' ? 'H' : 'M');

		// $col = $this->getColumnaEstrategica($jugador);
		// if ($this->max_tokens < 4) {
		// 	return $id_col;
		// } 
		// else if ($this->turno_maq) {

		// 	return $this->$fn_estrategia($jugador == 'M' ? 'H' : 'M');
		// }

		return false;
	}

	private function elegirConTrampa(string $jug = 'M')
	{
		for ($i = 0; $i < 4; $i++) {

			if ($i == 0) {
				if ($this->tablero[0][$i] == null && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && $this->tablero[3][3] == $jug) {
					return 0;
				}
				if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug && $this->tablero[3][$i] == null) {
					return 3;
				}
			} else {
				if($i == 1){
					if ($this->tablero[0][0] == $jug && ($this->tablero[1][$i] == null && $this->tablero[1][$i - 1] == null) && $this->tablero[2][2] == $jug && $this->tablero[3][3] == $jug) {
						return 1;
					}
					if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && ($this->tablero[2][$i] == null && $this->tablero[2][$i - 1] == null) && $this->tablero[3][0] == $jug) {
						return 2;
					}
				}

				if($i == 2){
					if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && ($this->tablero[2][2] == null && $this->tablero[2][$i - 1] == null) && $this->tablero[3][3] == $jug) {
						return 2;
					}
					if ($this->tablero[0][3] == $jug && ($this->tablero[1][$i] == null && $this->tablero[1][$i - 1] == null) && $this->tablero[2][1] == $jug && $this->tablero[3][0] == $jug) {
						return 1;
					}
				}

				if($i == 3){
					if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && ($this->tablero[3][3] == null && $this->tablero[3][$i - 1] == null)) {
						return 3;
					}
					if (($this->tablero[0][$i] == null && $this->tablero[0][$i - 1] == null) && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug && $this->tablero[3][0] == $jug) {
						return 0;
					}
				}

				if ($this->tablero[0][$i] == $jug	&& $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug	&& ($this->tablero[3][$i] == null && $this->tablero[3][$i - 1] == null)){
					return 3;
				}

				if ($this->tablero[0][$i] == $jug	&& $this->tablero[1][$i] == $jug && ($this->tablero[2][$i] == null && $this->tablero[2][$i - 1] == null) && $this->tablero[3][$i] == $jug){
					return 2;
				}

				if ($this->tablero[0][$i] == $jug && ($this->tablero[1][$i] == null && $this->tablero[1][$i - 1] == null) && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug){
					return 1;
				}

				if (($this->tablero[0][$i] == $jug && $this->tablero[0][$i - 1] == null) && $this->tablero[1][$i] == null && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug){
					return 0;
				}
			}
		}

		return false;
	}

	private function elegirGanador(): bool
	{
		$jug = $this->turno_maq ? "M" : "H";

		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug &&	$this->tablero[3][3] == $jug) {
			return true;
		}

		if ($this->tablero[3][0] == $jug &&	$this->tablero[2][1] == $jug &&	$this->tablero[1][2] == $jug &&	$this->tablero[0][3] == $jug) {
			return true;
		}

		for ($i = 0; $i < 4; $i++) {
			$vertical = $this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug &&	$this->tablero[$i][2] == $jug && $this->tablero[$i][3] == $jug;
			$horizontal = $this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug &&	$this->tablero[3][$i] == $jug;

			if ($vertical || $horizontal) return true;
		}

		return false;
	}

	private function elegirGanadorAutomatico()
	{

		$jug = $this->turno_maq ? "M" : "H";

		for ($i = 0; $i < 4; $i++) {

			if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == $jug) {
				return $i;
			}
		}

		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug) {
			return 3;
		}

		if ($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug) {
			return 0;
		}

		return false;
	}

	public function getBenderFriends()
	{
		$datos = json_decode(@file_get_contents(AMIGAS_FILE), true);
		$records = [
			'tops' => [],
			'records' => [],
			'error' => false
		];
		if ($datos != null) {

			foreach ($datos as $registro) {
				$amiga = AmigaBender::factory($registro);
				$top = $amiga->getTopRecord();
				if (!isset($records['tops'][$top['puntos']])) $records['tops'][$top['puntos']] = [];
				$records['tops'][$top['puntos']][] = $top;
				////$records['tops'][] = $amiga->getTopRecord();
				$records['records'][] = $amiga->toArray();
			}
		} else {
			$records['error'] = true;
		}

		return $records;
	}

	private function getEstrategiaBorracho(string $jug)
	{
		if ($this->max_tokens > 3) {
			// Diagonal 0-5-10-15
			if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && $this->tablero[3][2] !== null && $this->tablero[3][3] == null) {
				return 3;
			}

			// Diagonal 12-9-6-3
			if ($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug && $this->tablero[0][2] !== null && $this->tablero[0][3] == null) {
				return 0;
			}

			for ($i = 0; $i < 4; $i++) {
				// Vertical 4
				if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == $jug && $this->tablero[$i][3] == null) {
					return $i;
				}

				// Horizontal 4 0->3 - 3->0
				if ($i == 0) {
					if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == null) {
						return 3;
					}
					if ($this->tablero[3][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[0][$i] == null) {
						return 0;
					}
				} else {
					if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i - 1] != null && $this->tablero[3][$i] == null) {
						return 3;
					}
					if ($this->tablero[3][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[0][$i - 1] != null && $this->tablero[0][$i] == null) {
						return 0;
					}
				}
			}
		}
		// Diagonal 0-5-10
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][1] !== null && $this->tablero[2][2] == null) {
			return 2;
		}

		// Diagonal 12-9-6
		if ($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][1] !== null && $this->tablero[1][2] == null) {
			return 1;
		}

		for ($i = 0; $i < 4; $i++) {
			// Vertical 3
			if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == null) {
				return $i;
			}

			// Horizontal 3 0->2 - 3->1
			if ($i == 0) {
				if (
					$this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == null
					&& ($this->tablero[3][$i] == $jug || $this->tablero[3][$i] == null)
				) {
					return 2;
				}
				if (
					$this->tablero[3][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[1][$i] == null
					&& ($this->tablero[0][$i] == $jug || $this->tablero[0][$i] == null)
				) {
					return 1;
				}
			} else {
				if (
					$this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i - 1] != null
					&& $this->tablero[2][$i] == null && ($this->tablero[3][$i] == $jug || $this->tablero[3][$i] == null)
				) {
					return 2;
				}
				if (
					$this->tablero[3][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[1][$i - 1] != null
					&& $this->tablero[1][$i] == null && ($this->tablero[0][$i] == $jug || $this->tablero[0][$i] == null)
				) {
					return 1;
				}
			}
		}

		return false;
	}

	private function getEstrategiaFiestero(string $jug)
	{

		for ($i = 0; $i < 4; $i++) {

			// Horizontal dos a tres: 1-2
			if ($i == 0) {
				if ($this->tablero[0][$i] == null && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == null) {
					return rand(0, 1) ? 0 : 3;
				}
			} else {
				if (($this->tablero[0][$i] == null && $this->tablero[0][$i - 1] != null)
					&& $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug
					&& ($this->tablero[3][$i] == null && $this->tablero[3][$i - 1] != null)
				) {
					return $this->tablero[0][$i - 1] == $jug ? 0 : 3;
				}
			}
		}

		return $this->getEstrategiaBorracho($jug);
	}

	private function getEstrategiaResacoso(string $jug)
	{
		$nexts = $this->getNextsByMemory($jug);

		if (count($nexts) > 0) {

			$cols = $this->dificultad == 3 ? array_shift($nexts) : array_pop($nexts);

			$id_col = $cols[array_rand($cols)];
		} else {
			$id_col = $this->getEstrategiaFiestero($jug);
		}

		return $id_col;
	}

	private function getEstrategiaResacosoOld(string $jug)
	{
		$id_col = false;

		if ($jug == 'M') {
			$plays = json_decode(file_get_contents(MEM_FILE));
			$contra = $jug == 'H' ? 'M' : 'H';
			$tablero = array_map(function ($cell) use ($jug, $contra) {
				if ($cell == $contra) $cell = 2;
				else if ($cell == $jug) $cell = 1;
				else $cell = '';
				return $cell;
			}, array_merge(...$this->tablero));

			$nexts = [];
			foreach ($plays as $play) {
				$filt = array_filter($play[0]);
				$inicio = array_shift($filt) == 1 ? true : false;
				foreach ($play as $i => $move) {

					// Solo movimientos del oponente
					if ($inicio && $i % 2 == 0) continue;
					else if (!$inicio && $i % 2 != 0) continue;

					// tablero actual (sin movimiento) es move
					if (empty(array_diff_assoc($tablero, $move))) {
						$id_next = $i + 1; // Proximo movimiento registrado de jug
						$id_next_op = $i + 2; // Futuro movimiento de contra
						$id_col_aux = false;
						foreach ($play[$id_next] as $id_token => $next) {
							if ($tablero[$id_token] == '' && $next == 1) {
								$id_col_aux = $this->determinarColumna($id_token);
								break;
							}
						}

						if ($id_col_aux !== false) {
							$stepsVictory = count($play) - $id_next;

							if (!isset($nexts[$stepsVictory]))	$nexts[$stepsVictory] = [];

							if (!in_array($id_col_aux, $nexts[$stepsVictory]))
								$nexts[$stepsVictory][] = $id_col_aux;
						}
						break;
					}
				}
			}

			if (count($nexts) > 0) {
				if ($this->dificultad == 3) {
					$cols = array_shift($nexts);
				} else {
					$cols = array_pop($nexts);
				}
				$id_col = $cols[array_rand($cols)];
			}
		} else {
			$id_col = $this->getEstrategiaFiestero($jug);
		}

		return $id_col;
	}

	private function getLineaGanadora($tablero): array
	{
		$jug = $this->turno_maq ? "M" : "H";

		foreach ($this->triunfos as $triunfo) {
			$ok = true;
			foreach ($triunfo as $id_token) {
				if ($tablero[$id_token] != $jug) $ok = false;
			}
			if ($ok) return $triunfo;
		}
		return false;
	}

	private function getLosesByMemory(string $jug = 'H')
	{
		$exceps = [];

		$plays = $this->memory;
		// $plays = json_decode(@file_get_contents(MEM_FILE));
		if($plays){

			$contra = $jug == 'H' ? 'M' : 'H';
			$tablero = array_map(function ($cell) use ($jug, $contra) {
				if ($cell == $contra) $cell = 1;
				else if ($cell == $jug) $cell = 2;
				else $cell = '';
				return $cell;
			}, array_merge(...$this->tablero));

			foreach ($plays as $play) {
				$filt = array_filter($play[0]);
				$inicio = array_shift($filt) == 1 ? true : false;
				foreach ($play as $i => $move) {
					
					// Saltamos movimientos del ganador
					if ($inicio && $i % 2 != 0) continue;
					else if (!$inicio && $i % 2 == 0) continue;
					
					if ($tablero == $move) {
						$id_next = $i + 1; // Proximo movimiento registrado de jug
						$id_next_op = $i + 2; // Futuro movimiento de contra
						$id_col_aux = false;
						foreach ($play[$id_next] as $id_token => $next) {
							if ($tablero[$id_token] == '' && $next == 2) {
								$id_col_aux = $this->determinarColumna($id_token);
								break;
							}
						}

						if ($id_col_aux !== false) {
							if(in_array($id_col_aux, $exceps) === false) $exceps[] = $id_col_aux;
						}
						break;
					}
				}
			}

			if (count($exceps) > 0) sort($exceps);
		}

		return $exceps;
	}

	public function getKillsByMemory(): array
	{
		$memoria = json_decode(@file_get_contents(MEM_FILE));
		$salida = [];
		foreach ($memoria as $i => $play) {
			$last = $play[count($play) - 1];

			$last = array_map(function ($cell) {
				if ($cell == 1) $cell = 'M';
				else if ($cell == 2) $cell = 'H';
				else $cell = null;
				return $cell;
			}, $last);

			if(!in_array($last, $salida)) $salida[] = $last;
		}

		//$salida = array_filter($salida, function($a, $b) { return $a != $b; });
		return $salida;
	}

	private function getNextsByMemory(string $jug)
	{
		$nexts = [];
		$plays = $this->memory;
		// $plays = json_decode(@file_get_contents(MEM_FILE));

		if ($plays) {
			$contra = $jug == 'M' ? 'H' : 'M';
			$tablero = array_map(function ($cell) use ($jug, $contra) {
				if ($cell == $contra) $cell = 2;
				else if ($cell == $jug) $cell = 1;
				else $cell = '';
				return $cell;
			}, array_merge(...$this->tablero));

			foreach ($plays as $play) {
				$filt = array_filter($play[0]);
				$inicio = array_shift($filt) == 1 ? true : false;
				foreach ($play as $i => $move) {

					// Saltamos movimientos del oponente
					if ($inicio && $i % 2 == 0) continue;
					else if (!$inicio && $i % 2 != 0) continue;

					if ($tablero == $move) {
						$id_next = $i + 1; // Proximo movimiento registrado de jug
						$id_next_op = $i + 2; // Futuro movimiento de contra
						$id_col_aux = false;
						foreach ($play[$id_next] as $id_token => $next) {
							if ($tablero[$id_token] == '' && $next == 1) {
								$id_col_aux = $this->determinarColumna($id_token);
								break;
							}
						}

						if ($id_col_aux !== false) {
							$stepsVictory = count($play) - $id_next;

							if (!isset($nexts[$stepsVictory]))	$nexts[$stepsVictory] = [];

							if (!in_array($id_col_aux, $nexts[$stepsVictory]))
								$nexts[$stepsVictory][] = $id_col_aux;
						}
						break;
					}
				}
			}

			if (count($nexts) > 0) ksort($nexts);
		}

		return $nexts;
	}

	private function getTempPlay()
	{
		$datos = json_decode(@file_get_contents(BASE_TEMP . $this->temp_file));
		if (!$datos) $datos = [];

		return $datos;
	}

	private function guardarAmistadBender(string $nombre, string $ganador)
	{
		$datos = json_decode(@file_get_contents(AMIGAS_FILE), true);
		$amiga = false;
		$ind = false;
		if ($datos != null) {

			foreach ($datos as $i => $registro) {
				if ($registro['nombre'] == $nombre) {
					$amiga = AmigaBender::factory($registro);
					$ind = $i;
					break;
				}
			}
		} else {
			$datos = [];
		}

		if (!$amiga) $amiga = new AmigaBender($nombre);

		$amiga->actualizarNumeros($this->dificultad, $ganador);

		if ($ind !== false) $datos[$ind] = $amiga->toArray();
		else $datos[] = $amiga->toArray();

		return file_put_contents(AMIGAS_FILE, json_encode($datos));
	}

	private function guardarMovimiento(): int
	{
		$tablero = array_merge(...$this->tablero);

		if (count(array_filter($tablero)) == 1) {
			file_put_contents(BASE_TEMP . $this->temp_file, '');
			// file_put_contents(MEM_TEMP_FILE, '');
			$datos = [];
		} else {
			$str_datos = @file_get_contents(BASE_TEMP . $this->temp_file);
			// $str_datos = @file_get_contents(MEM_TEMP_FILE);
			$datos = json_decode($str_datos);
		}

		if (!$datos) $datos = [];

		$datos[] = $tablero;

		return file_put_contents(BASE_TEMP . $this->temp_file, json_encode($datos));
		// return file_put_contents(MEM_TEMP_FILE, json_encode($datos));
	}

	public static function guardarPartida(array $partida, int $ind_ganador): int
	{
		if ($partida && count($partida) > 0) {
			$datos = json_decode(@file_get_contents(MEM_FILE));
			if (!$datos) $datos = [];

			usort($datos, function ($a, $b) {
				if (count($a) == count($b)) {
					return 0;
				}

				return (count($a) < count($b)) ? -1 : 1;
			});


			$ganador = $ind_ganador % 2 == 0 ? 'H' : 'M';
			$perdedor = $ganador == 'H' ? 'M' : 'H';

			for ($i = 0; $i < count($partida); $i++) {
				for ($j = 0; $j < count($partida[$i]); $j++) {
					if ($partida[$i][$j] == $ganador) $partida[$i][$j] = 1;
					else if ($partida[$i][$j] == $perdedor) $partida[$i][$j] = 2;
					else $partida[$i][$j] = '';
				}
			}

			$repetida = false;
			foreach ($datos as $play) {
				if ($play == $partida) {
					$repetida = true;
					break;
				}
			}

			if (!$repetida) {
				$datos[] = $partida;
				return file_put_contents(MEM_FILE, json_encode($datos));
			}
		}

		return false;
	}

	public function iniciarJuegoAutomatico()
	{
		$this->max_tokens = 3;
		$this->turno_maq = $this->iniciarTurno();

		$arr_mensaje = array(
			"Partida automática",
			"Yo llevo las amarillas y tu las verdes.",
			($this->turno_maq ? "El azar ha decidido que empiezo yo." : "El azar quiere que empieces!")
		);

		$ganador = false;
		for ($i = 0; $i < 12; $i++) {

			$tokenElegido = false;
			while ($tokenElegido === false) {
				$id_col = $this->elegirColumna();
				$tokenElegido = $this->anadirTokenAColumna($id_col);
			}

			if ($this->elegirGanador()) {
				$ganador = $this->turno_maq ? 'M' : 'H';
				break;
			}
			$this->turno_maq = !$this->turno_maq;
		}

		$col_ganadora = $this->elegirGanadorAutomatico();

		if ($col_ganadora !== false) {
			$this->max_tokens = 4;
			$token = $this->anadirTokenAColumna($col_ganadora);
		} else {
			$token = false;
		}

		if ($ganador) {
			if ($ganador == 'H') {
				$arr_mensaje[] = 'Los Hados quisieron que ganarás antes de la última fila, biológico.';
			} else {
				$arr_mensaje[] = 'He ganado antes de la última fila.';
				$arr_mensaje[] = 'La Fortuna lo quiso así.';
			}
		} else {
			if ($this->turno_maq) {
				$arr_mensaje[] = "El próximo movimiento es mio.";
				if ($col_ganadora !== false) {
					$arr_mensaje[] = "Gano jugando en la columna " . ($col_ganadora + 1);
				} else {
					$arr_mensaje[] = "No puedo ganar con mi próximo movimiento.";
				}
			} else {
				$arr_mensaje[] = "El próximo movimiento es tuyo!";
				if ($col_ganadora !== false) {
					$arr_mensaje[] = "Puedes ganar si juegas en la columna " . ($col_ganadora + 1);
				} else {
					$arr_mensaje[] = "No puedes ganar con tu próximo movimiento.";
				}
			}
		}

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Jugamos otra vez?";

		return array(
			'tablero' => array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
			'token' => $token
		);
	}

	public function iniciarJuegoAprendizaje(int $rounds = 1)
	{
		$this->max_tokens = 4;
		$this->dificultad = 3;
		$this->turno_maq = $this->iniciarTurno();

		$arr_mensaje = array(
			"Partida de aprendizaje - Nivel " . ($this->dificultad === 3 ? "Sobrio" : "irrelevante"),
			"Yo llevo las amarillas y tu las verdes, que estás aprendiendo, nudillos de hueso.",
			"",
		);

		$human = 0;
		$machine = 0;
		$draws = 0;
		$saved = 0;
		$total = $rounds;
		while ($rounds > 0) {

			$this->memory = json_decode(@file_get_contents(MEM_FILE));
			$this->limpiar_tablero();

			$ganador = false;
			$partida = [];
			for ($i = 0; $i < ($this->max_tokens * 4); $i++) {

				$tokenElegido = false;
				while ($tokenElegido === false) {
					// $id_col = $this->elegirColumna();
					if($this->turno_maq) {
						$id_col = $this->elegirColumna();
					} else {
						$id_col = $this->elegirColumnaAprendizaje();
					}
					$tokenElegido = $this->anadirTokenAColumna($id_col);
				}

				$partida[] = array_merge(...$this->tablero);

				if ($this->elegirGanador()) {
					$ganador = $this->turno_maq ? 'M' : 'H';
					break;
				}
				$this->turno_maq = !$this->turno_maq;
			}

			if ($ganador !== false) {

				$ganador == 'H' ? $human++ : $machine++;

				$bytes = Cuatro::guardarPartida($partida, $ganador == 'M' ? 1 : 2);
				if($bytes) $saved++;

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
		
		$plays = json_decode(file_get_contents(MEM_FILE));
		$arr_mensaje[] = "** Hay " . count($plays) . " partidas guardadas **";

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Le damos caña otra vez?";

		return array(
			'tablero' => array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
		);
	}

	public function iniciarJuegoSolitario()
	{
		$this->max_tokens = 4;
		$this->dificultad = 3;
		$this->turno_maq = $this->iniciarTurno();

		$arr_mensaje = array(
			// "Partida en solitario - Nivel Borracho",
			"Partida en solitario - Nivel " . ($this->dificultad === 3 ? "Sobrio" : "irrelevante"),
			"Yo llevo las amarillas y tu las verdes.",
			($this->turno_maq ? "El azar ha decidido que empiezo yo." : "El azar quiere que empieces!")
		);

		$ganador = false;
		$partida = [];
		for ($i = 0; $i < ($this->max_tokens * 4); $i++) {

			$tokenElegido = false;
			while ($tokenElegido === false) {
					$id_col = $this->elegirColumna();

				$tokenElegido = $this->anadirTokenAColumna($id_col);
			}
			$partida[] = array_merge(...$this->tablero);

			if ($this->elegirGanador()) {
				$ganador = $this->turno_maq ? 'M' : 'H';
				break;
			}
			$this->turno_maq = !$this->turno_maq;
		}

		if ($ganador) {
			$ind = 1;
			if ($ganador == 'H') {
				$arr_mensaje[] = 'Los Hados quisieron que ganarás, biológico.';
				$ind = 2;
			} else {
				$arr_mensaje[] = 'He ganado. La Fortuna lo quiso así.';
			}

			$bytes = Cuatro::guardarPartida($partida, $ganador == 'M' ? 1 : 2);

			$arr_mensaje[] = "Guardados $bytes bytes";
		} else {
			$arr_mensaje[] = "Parece que tenemos tablas";
		}

		$plays = json_decode(file_get_contents(MEM_FILE));
		$arr_mensaje[] = "Hay " . count($plays) . " guardadas";

		$arr_mensaje[] = "";
		$arr_mensaje[] = "¿Jugamos otra vez?";

		return array(
			'tablero' => array_merge($this->tablero[0], $this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
		);
	}

	public function iniciarPartida(int $dificultad)
	{
		$this->max_tokens = 4;
		$this->turno_maq = $this->iniciarTurno();
		$this->dificultad = $dificultad;
		$this->temp_file = 'play_' . (new DateTime())->format('Uu') . '.txt';

		$arr_mensaje = array(
			"Partida manual",
			"Yo llevo las fichas amarillas y tu las fichas verdes.",
		);

		if ($this->turno_maq) {
			$col_maquina = $this->elegirColumna();
			$this->anadirTokenAColumna($col_maquina);
			$arr_mensaje[] = "El azar ha decidido que empiezo yo.";
			$arr_mensaje[] = 'He jugado en la columna ' . ($col_maquina + 1);
			$this->guardarMovimiento();
		} else {
			$arr_mensaje[] = "El azar quiere que empieces!";
			$arr_mensaje[] = "Elige una columna";
		}

		return array(
			'tablero' => array_merge($this->tablero[0],	$this->tablero[1], $this->tablero[2],	$this->tablero[3]),
			'mensaje' => $arr_mensaje,
			'temp_file' => $this->temp_file
		);
	}

	public function iniciarTurno(): bool
	{
		return rand() % 2 == 0;
	}

	private function limpiar_tablero()
	{
		$this->tablero = [];
		for($i = 0; $i < 4; $i++) {
			$aux = [];
			for($j = 0; $j < 4; $j++) $aux[] = null;
			$this->tablero[] = $aux;
		}
	}

	private function transform_tablero(array $tablero) {
		//if()
	}

	private function ordenarArrayPorElementos($array, $orden = "asc")
	{
		usort($array, function ($a, $b) use ($orden) {
			if (count($a) == count($b)) {
				return 0;
			}
			if ($orden != "asc") {

				return (count($a) > count($b)) ? -1 : 1;
			}

			return (count($a) < count($b)) ? -1 : 1;
		});
	}
}
