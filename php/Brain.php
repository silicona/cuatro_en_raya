<?php

namespace CuatroPhp\php;

class Brain {

  private int $max_tokens = 4;
  private array $tablero;
  private ?array $memory = null;

  public function __construct()
  {
  }

  public function anadirTokenAColumna(int $id_columna, string $jugador = 'M')
	{
		for ($i = 0; $i < $this->max_tokens; $i++) {

			if ($this->tablero[$id_columna][$i] == null) {

				$this->tablero[$id_columna][$i] = $jugador;

				return $this->determinarToken($id_columna, $i);
			}
		}

		return false;
	}

	public function determinarColumna(int $id_token): int
	{
		return ceil(++$id_token / 4) - 1;
	}

	private function determinarToken(int $id_columna, int $altura): int
	{
		$posiciones = array_chunk(range(0, 15), 4);
		return $posiciones[$id_columna][$altura];
	}

	public function elegirColumna(string $jug = 'M', int $dificultad = 0)
	{
		//$jugador = $this->turno_maq ? "M" : "H";
		switch ($dificultad) {
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

		return $this->$fn_estrategia($jug, $dificultad);
	}

	public function elegirColumnaAleatoria(array $exceps = [])
	{
		if (count($exceps) > 3) $exceps = [];

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

				if (!empty($exceps)) {
					$arr_cols = $exceps;
					$exceps = [];
				} else {
					break;
				}
			}
		}

		return $id_col;
	}

  public function elegirColumnaAprendizaje(string $jug = 'H')
	{
		$exceps = $this->getLosesByMemory($jug);

		// $id_col_trampa = $this->elegirConTrampa($jug == 'H' ? 'M' : 'H');
		// if ($id_col_trampa !== false && !in_array($id_col_trampa, $exceps)) $exceps[] = $id_col_trampa;

		$id_col_a_evitar = $this->getEstrategiaFiestero($jug == 'H' ? 'M' : 'H');

		if ($id_col_a_evitar !== false && !in_array($id_col_a_evitar, $exceps)) {
			$exceps[] = $id_col_a_evitar;
			sort($exceps);
		}

		$col_elegida = $this->elegirColumnaAleatoria($exceps);

		return $col_elegida;
	}

  public function elegirGanador(string $jug = 'M'): bool
	{
		//$jug = $this->turno_maq ? "M" : "H";

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

  public function elegirGanadorAutomatico(string $jug = 'M')
	{

		//$jug = $this->turno_maq ? "M" : "H";

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

  private function get3enRaya(string $jug = 'M')
	{
		// Diagonal 0/5-10/15
		if ($this->tablero[0][0] == null  && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug) return 0;
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][1] !== null && $this->tablero[2][2] == null) return 2;
		if ($this->tablero[3][3] == $jug && $this->tablero[2][2] == $jug && ($this->tablero[1][0] !== null && $this->tablero[1][1] == null)) return 1;
		if (($this->tablero[3][2] != null && $this->tablero[3][3] == null) && $this->tablero[2][2] == $jug && $this->tablero[1][1] == $jug) return 3;

		// Diagonal 3/6-9/12
		if ($this->tablero[0][3] == null  && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug) return 0;
		if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && $this->tablero[2][0] !== null && $this->tablero[2][1] == null) return 2;
		if ($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && ($this->tablero[1][1] !== null && $this->tablero[1][2] == null)) return 1;
		if ($this->tablero[3][0] == null && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug) return 3;

		for ($i = 0; $i < 4; $i++) {

			// Vertical 3
			if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == null) return $i;

			// Horizontal dos a tres: 1-2
			if ($i == 0) {
				if ($this->tablero[0][$i] == null && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == null) return rand(0, 1) ? 0 : 3;
			} else {
				if (($this->tablero[0][$i] == null && $this->tablero[0][$i - 1] != null)
					&& $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug
					&& ($this->tablero[3][$i] == null && $this->tablero[3][$i - 1] != null)
				) return $this->tablero[0][$i - 1] == $jug ? 0 : 3;
			}

			// Horizontal 3 0->2 - 3->1
			if ($i == 0) {
				if ($this->tablero[0][0] == $jug && $this->tablero[1][0] == $jug && $this->tablero[2][0] == null	&& ($this->tablero[3][0] == $jug || $this->tablero[3][0] == null)) return 2;

				if ($this->tablero[3][0] == $jug && $this->tablero[2][0] == $jug && $this->tablero[1][0] == null	&& ($this->tablero[0][0] == $jug || $this->tablero[0][0] == null)) return 1;
			} else {
				if (
					$this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug
					&& $this->tablero[2][$i - 1] != null	&& $this->tablero[2][$i] == null
					&& ($this->tablero[3][$i] == $jug || $this->tablero[3][$i] == null)
				)	return 2;

				if (
					$this->tablero[3][$i] == $jug && $this->tablero[2][$i] == $jug
					&& $this->tablero[1][$i - 1] != null	&& $this->tablero[1][$i] == null
					&& ($this->tablero[0][$i] == $jug || $this->tablero[0][$i] == null)
				)	return 1;
			}
		}

		return false;
	}

	private function get4enRaya(string $jug = 'M')
	{
		// Diagonal 0-5-10-15
		if ($this->tablero[0][0] == null && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && $this->tablero[3][3] == $jug) return 0;
		if ($this->tablero[0][0] == $jug && ($this->tablero[1][0] !== null && $this->tablero[1][1] == null) && $this->tablero[2][2] == $jug && $this->tablero[3][3] == $jug) return 1;
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && ($this->tablero[2][1] !== null && $this->tablero[2][2] == null) && $this->tablero[3][3] == $jug) return 2;
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && ($this->tablero[3][2] !== null && $this->tablero[3][3] == null)) return 3;

		// Diagonal 3-6-9-12	
		if (($this->tablero[0][2] != null && $this->tablero[0][3] == null) && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug && $this->tablero[3][0] == $jug) return 0;
		if ($this->tablero[0][3] == $jug && ($this->tablero[1][1] !== null && $this->tablero[1][2] == null) && $this->tablero[2][1] == $jug && $this->tablero[3][0] == $jug) return 1;
		if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && ($this->tablero[2][0] !== null && $this->tablero[2][1] == null) && $this->tablero[3][0] == $jug) return 2;
		if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug && $this->tablero[3][0] == null) return 3;


		for ($i = 0; $i < 4; $i++) {
			// Vertical 4
			if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == $jug && $this->tablero[$i][3] == null) return $i;

			// Horizontal 4 0->3 - 3->0
			if ($i == 0) {
				if ($this->tablero[0][$i] == null && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 0;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == null && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 1;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == null && $this->tablero[3][$i] == $jug) return 2;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == null) return 3;
			} else {
				if (($this->tablero[0][$i - 1] != null && $this->tablero[0][$i] == null) && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 0;
				if ($this->tablero[0][$i] == $jug && ($this->tablero[1][$i - 1] != null && $this->tablero[1][$i] == null) && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 1;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && ($this->tablero[2][$i - 1] != null && $this->tablero[2][$i] == null) && $this->tablero[3][$i] == $jug) return 2;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && ($this->tablero[3][$i - 1] != null && $this->tablero[3][$i] == null)) return 3;
			}
		}

		return false;
	}

	private function get4enRayaHorizontal(string $jug = 'M')
	{
		for ($i = 0; $i < 4; $i++) {
			// Horizontal 4 0->3 - 3->0
			if ($i == 0) {
				if ($this->tablero[0][$i] == null && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 0;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == null && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 1;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == null && $this->tablero[3][$i] == $jug) return 2;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == null) return 3;
			} else {
				if (($this->tablero[0][$i - 1] != null && $this->tablero[0][$i] == null) && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 0;
				if ($this->tablero[0][$i] == $jug && ($this->tablero[1][$i - 1] != null && $this->tablero[1][$i] == null) && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 1;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && ($this->tablero[2][$i - 1] != null && $this->tablero[2][$i] == null) && $this->tablero[3][$i] == $jug) return 2;
				if ($this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && ($this->tablero[3][$i - 1] != null && $this->tablero[3][$i] == null)) return 3;
			}
		}

		return false;
	}

	private function get4enRayaTrampa(string $jug = 'M')
	{
		// Diagonal
		if ($this->tablero[0][0] == $jug && ($this->tablero[1][1] == null && $this->tablero[1][0] == null) && $this->tablero[2][2] == $jug && $this->tablero[3][3] == $jug) return 1;
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && ($this->tablero[2][2] == null && $this->tablero[2][1] == null) && $this->tablero[3][3] == $jug) return 2;
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && ($this->tablero[3][3] == null && $this->tablero[3][2] == null)) return 3;

		if (($this->tablero[0][3] == null && $this->tablero[0][2] == null) && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug && $this->tablero[3][0] == $jug) return 0;
		if ($this->tablero[0][3] == $jug && ($this->tablero[1][2] == null && $this->tablero[1][1] == null) && $this->tablero[2][1] == $jug && $this->tablero[3][0] == $jug) return 1;
		if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && ($this->tablero[2][1] == null && $this->tablero[2][2] == null) && $this->tablero[3][0] == $jug) return 2;

		for ($i = 1; $i < 4; $i++) {

			// Horizontales
			if (($this->tablero[0][$i] == $jug && $this->tablero[0][$i - 1] == null) && $this->tablero[1][$i] == null && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 0;
			if ($this->tablero[0][$i] == $jug && ($this->tablero[1][$i] == null && $this->tablero[1][$i - 1] == null) && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == $jug) return 1;
			if ($this->tablero[0][$i] == $jug	&& $this->tablero[1][$i] == $jug && ($this->tablero[2][$i] == null && $this->tablero[2][$i - 1] == null) && $this->tablero[3][$i] == $jug) return 2;
			if ($this->tablero[0][$i] == $jug	&& $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug	&& ($this->tablero[3][$i] == null && $this->tablero[3][$i - 1] == null)) return 3;
		}

		return false;
	}

  public function getEstrategiaAutomatica(string $jug)
	{
		$id_col = $this->get4enRayaHorizontal($jug);

		if ($id_col === false) $id_col = $this->get3enRaya($jug);

		if ($id_col === false) $id_col = $this->elegirColumnaAleatoria();

		return $id_col;
	}

  public function getEstrategiaBorracho(string $jug)
	{
		$id_col = $this->get4enRaya($jug);

		if ($id_col === false) $id_col = $this->get3enRaya($jug);

		if ($id_col === false) $id_col = $this->elegirColumnaAleatoria();

		return $id_col;
	}

	public function getEstrategiaFiestero(string $jug)
	{
		$id_col = $this->get4enRaya($jug);

		if ($id_col === false) {
			$id_col_trampa = $this->get4enRayaTrampa($jug);
			if($id_col_trampa !== false) return $this->elegirColumnaAleatoria([$id_col_trampa]);
		}

		if ($id_col === false) $id_col = $this->get4enRaya($jug == 'M' ? 'H' : 'M');

		if ($id_col === false) $id_col = $this->get3enRaya($jug);

		if ($id_col === false) {
			$excep = [];
			$id_col_mortal = $this->get4enRayaTrampa($jug == 'M' ? 'H' : 'M');
			if($id_col_mortal !== false) $excep[] = $id_col_mortal;

			$id_col = $this->elegirColumnaAleatoria($excep);
		}

		return $id_col;
	}

  public function getEstrategiaResacoso(string $jug, int $dificultad = 2)
	{
		$nexts = $this->getNextsByMemory($jug);

		if (count($nexts) > 0) {

			$cols = $dificultad == 3 ? array_shift($nexts) : array_pop($nexts);

			$id_col = $cols[array_rand($cols)];
		} else {
			$id_col = $this->getEstrategiaFiestero($jug);
		}

		return $id_col;
	}

  public function getLineaGanadora($tablero, string $jug = 'M'): array
	{
		// $jug = $this->turno_maq ? "M" : "H";

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
		if ($plays) {

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
							if (in_array($id_col_aux, $exceps) === false) $exceps[] = $id_col_aux;
						}
						break;
					}
				}
			}

			if (count($exceps) > 0) sort($exceps);
		}

		return $exceps;
	}

  public function getMemory(){
    return $this->memory;
  }

  private function getNextsByMemory(string $jug = 'M')
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

  public function getTableroMerged(){
    return array_merge(...$this->tablero);
  }

  public function limpiar_tablero()
	{
		$this->tablero = [];
		for ($i = 0; $i < 4; $i++) {
			$aux = [];
			for ($j = 0; $j < 4; $j++) $aux[] = null;
			$this->tablero[] = $aux;
		}
	}

  public function setMaxtokens(int $max){
    $this->max_tokens = $max;
  }

  public function setMemory(?array $memory){
    $this->memory = $memory;
  }

  public function setTablero(array $tablero){
    $this->tablero = $tablero;
  }

}