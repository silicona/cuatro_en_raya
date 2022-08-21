<?php

namespace CuatroPhp\php;

class Brain
{

	private int $max_tokens = 4;
	private array $tablero;
	private ?array $memory = null;

	public function __construct()
	{
	}

	public function anadirTokenAColumna($id_columna, string $jugador = 'M')
	{
		if ($id_columna !== false) {
			for ($i = 0; $i < $this->max_tokens; $i++) {

				if ($this->tablero[$id_columna][$i] == null) {

					$this->tablero[$id_columna][$i] = $jugador;

					return $this->determinarToken($id_columna, $i);
				}
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

	public function elegirColumnaAutomatica(string $jugador = 'M')
	{
		$id_col = $this->getEstrategiaAutomatica($jugador);

		if ($id_col === false) $id_col = $this->elegirColumnaAleatoria();

		return $id_col;
	}

	public function elegirColumnaPorCalculo(string $jugador = 'M', int $dificultad = 0)
	{
		$datos = $this->getEstrategiaCalculada($jugador, $dificultad);

		if ($datos['data']['num_fin'] == 1) {
			$salida = [
				'ok' => true,
				'id_col' => $this->getColumnaMasUsada($datos['data']['id_cols'])
			];
		} elseif ($datos['datako']['num_fin'] == 1) {
			$salida = [
				'ok' => false,
				'num_fin' => $datos['datako']['num_fin'],
				'id_col' => $datos['datako']['id_cols'][0]
			];
		} elseif ($datos['data']['num_fin'] > $datos['datako']['num_fin'] && $datos['datako']['num_fin'] < 5) {
			$salida = [
				'ok' => false,
				'num_fin' => $datos['datako']['num_fin'],
				'id_col' => $this->elegirColumnaAleatoria($datos['datako']['id_cols'])
			];
		} elseif ($datos['data']['num_fin'] < 17) {
			$salida = [
				'ok' => true,
				'num_fin' => $datos['data']['num_fin'],
				'id_col' => $this->getColumnaMasUsada($datos['data']['id_cols'], $dificultad < 3 ? false : true)
			];
		} else {
			$salida = [
				'ok' => false,
				'id_col' => $this->elegirColumnaAleatoria($datos['datako']['id_cols'])
			];
		}

		return $salida;
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
		for ($i = 0; $i < 4; $i++) {

			if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == $jug) return $i;
		}

		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug) return 3;

		if ($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug) return 0;

		return false;
	}

	private function get2enRaya(string $jug = 'M')
	{
		$tab = $this->tablero;
		$checks = [
			['bool' => $tab[0][0] == null && $tab[1][0] = null, 'col' => 0],
			['bool' => $tab[0][0] == $jug && $tab[0][1] = null, 'col' => 0],
			['bool' => $tab[2][0] == null && $tab[3][0] = null, 'col' => 3],
			['bool' => $tab[3][0] == $jug && $tab[3][1] = null, 'col' => 3],
		];

		shuffle($checks);

		foreach($checks as $check){
			if($check['bool']) return $check['col'];
		}

		return false;
	}

	private function get3enRaya(string $jug = 'M')
	{
		$tab = $this->tablero;
		$checks = [
			['bool' => $tab[0][0] == null && $tab[1][0] != null && $tab[1][1] == null && $tab[2][2] == $jug && $tab[3][3] == $jug, 'col' => rand(0, 1) ? 0 : 1],
			['bool' => $tab[0][0] == null && $tab[1][1] == null && $tab[2][2] == $jug && $tab[3][3] == $jug, 'col' => 0],
			['bool' => $tab[0][0] == null && $tab[1][1] == $jug && $tab[2][2] == $jug && $tab[3][2] != null && $tab[3][3] == null, 'col' => rand(0, 1) ? 0 : 3],
			['bool' => $tab[0][0] == null && $tab[1][1] == $jug && $tab[2][2] == $jug && $tab[3][3] == null, 'col' => 0],
			['bool' => $tab[0][0] == $jug && $tab[1][0] != null && $tab[1][1] == null && $tab[2][2] == $jug && $tab[3][2] != null && $tab[3][3] == null, 'col' => rand(0, 1) ? 1 : 3],
			['bool' => $tab[0][0] == $jug && $tab[1][1] == null && $tab[2][2] == $jug && $tab[3][2] != null && $tab[3][3] == null, 'col' => 3],
			['bool' => $tab[0][0] == $jug && $tab[1][0] != null && $tab[1][1] == null && $tab[2][2] == $jug && $tab[3][3] == null, 'col' => 1],
			['bool' => $tab[0][0] == $jug && $tab[1][1] == $jug && $tab[2][1] != null && $tab[2][2] == null && $tab[3][2] != null && $tab[3][3] == null, 'col' => rand(0, 1) ? 2 : 3],
			['bool' => $tab[0][0] == $jug && $tab[1][1] == $jug && $tab[2][1] != null && $tab[2][2] == null && $tab[3][3] == null, 'col' => 2],
			['bool' => $tab[0][0] == $jug && $tab[1][1] == $jug && $tab[2][2] == null && $tab[3][2] != null && $tab[3][3] == null, 'col' => 3],

			['bool' => $tab[3][0] == null && $tab[2][0] != null && $tab[2][1] == null && $tab[1][2] == $jug && $tab[0][3] == $jug, 'col' => rand(0, 1) ? 3 : 2],
			['bool' => $tab[3][0] == null && $tab[2][1] == null && $tab[1][2] == $jug && $tab[0][3] == $jug, 'col' => 3],
			['bool' => $tab[3][0] == null && $tab[2][1] == $jug && $tab[1][2] == $jug && $tab[0][2] != null && $tab[0][3] == null, 'col' => rand(0, 1) ? 3 : 0],
			['bool' => $tab[3][0] == null && $tab[2][1] == $jug && $tab[1][2] == $jug && $tab[0][3] == null, 'col' => 3],
			['bool' => $tab[3][0] == $jug && $tab[2][0] != null && $tab[2][1] == null && $tab[1][2] == $jug && $tab[0][2] != null && $tab[0][3] == null, 'col' => rand(0, 1) ? 2 : 0],
			['bool' => $tab[3][0] == $jug && $tab[2][1] == null && $tab[1][2] == $jug && $tab[0][2] != null && $tab[0][3] == null, 'col' => 0],
			['bool' => $tab[3][0] == $jug && $tab[2][0] != null && $tab[2][1] == null && $tab[1][2] == $jug && $tab[0][3] == null, 'col' => 2],
			['bool' => $tab[3][0] == $jug && $tab[2][1] == $jug && $tab[1][1] != null && $tab[1][2] == null && $tab[0][2] != null && $tab[0][3] == null, 'col' => rand(0, 1) ? 1 : 0],
			['bool' => $tab[3][0] == $jug && $tab[2][1] == $jug && $tab[1][1] != null && $tab[1][2] == null && $tab[0][3] == null, 'col' => 1],
			['bool' => $tab[3][0] == $jug && $tab[2][1] == $jug && $tab[1][2] == null && $tab[0][2] != null && $tab[0][3] == null, 'col' => 0],

		];

		for ($i = 0; $i < 4; $i++) {

			// Vertical 3
			$checks[] = ['bool' => $tab[$i][0] == $jug && $tab[$i][1] == $jug && $tab[$i][2] == null, 'col' => $i];

			// Horizontal dos a tres: 1-2
			if ($i == 0) {
				$checks[] = ['bool' => $tab[0][0] == null && $tab[1][0] == $jug && $tab[2][0] == $jug && $tab[3][0] == null, 'col' => rand(0, 1) ? 0 : 3];
				$checks[] = ['bool' => $tab[0][0] == null && $tab[1][0] == null && $tab[2][0] == $jug && $tab[3][0] == $jug, 'col' => rand(0, 1) ? 0 : 1];
				//$check[] = ['bool' => $tab[0][0] == $jug && $tab[1][0] == null && $tab[2][0] == null && $tab[3][0] == $jug, 'col' => rand(0, 1) ? 1 : 2];
				$checks[] = ['bool' => $tab[0][0] == $jug && $tab[1][0] == $jug && $tab[2][0] == null && $tab[3][0] == null, 'col' => rand(0, 1) ? 2 : 3];
			} else {

				$checks[] = ['bool' => $tab[0][$i - 1] != null && $tab[0][$i] == null && $tab[1][$i] == $jug && $tab[2][$i] == $jug && $tab[3][$i - 1] != null && $tab[3][$i] == null,	'col' => rand(0, 1) ? 0 : 3];
				$checks[] = ['bool' => $tab[0][$i] == null && $tab[1][$i] == $jug && $tab[2][$i] == $jug && $tab[3][$i - 1] != null && $tab[3][$i] == null,	'col' => 3];
				$checks[] = ['bool' => $tab[0][$i - 1] != null && $tab[0][$i] == null && $tab[1][$i] == $jug && $tab[2][$i] == $jug && $tab[3][$i] == null,	'col' => 0];

				$checks[] = ['bool' => $tab[0][$i - 1] != null && $tab[0][$i] == null && $tab[1][$i - 1] != null	&& $tab[1][$i] == null && $tab[2][$i] == $jug && $tab[3][$i] == $jug, 'col' => rand(0, 1) ? 0 : 1];
				$checks[] = ['bool' => $tab[0][$i - 1] != null && $tab[0][$i] == null && $tab[1][$i] == null && $tab[2][$i] == $jug && $tab[3][$i] == $jug, 'col' => 0];
				$checks[] = ['bool' => $tab[0][$i] == null && $tab[1][$i - 1] != null	&& $tab[1][$i] == null && $tab[2][$i] == $jug && $tab[3][$i] == $jug, 'col' => 1];

				$checks[] = ['bool' => $tab[0][$i] == $jug && $tab[1][$i] == $jug && $tab[2][$i - 1] != null	&& $tab[2][$i] == null && $tab[3][$i - 1] != null && $tab[3][$i] == null, 'col' => rand(0, 1) ? 2 : 3];
				$checks[] = ['bool' => $tab[0][$i] == $jug && $tab[1][$i] == $jug && $tab[2][$i - 1] != null	&& $tab[2][$i] == null && $tab[3][$i] == null, 'col' => 2];
				$checks[] = ['bool' => $tab[0][$i] == $jug && $tab[1][$i] == $jug && $tab[2][$i] == null && $tab[3][$i - 1] != null && $tab[3][$i] == null, 'col' => 3];
			}
		}

		shuffle($checks);

		foreach($checks as $check){
			if($check['bool']) return $check['col'];
		}

		return false;
	}

	private function get4enRaya(string $jug = 'M')
	{
		$tab = $this->tablero;

		$checks = [
			['bool' => $tab[0][0] == null && $tab[1][1] == $jug && $tab[2][2] == $jug && $tab[3][3] == $jug, 'col' => 0],
			['bool' => $tab[0][0] == $jug && $tab[1][0] !== null && $tab[1][1] == null && $tab[2][2] == $jug && $tab[3][3] == $jug, 'col' => 1],
			['bool' => $tab[0][0] == $jug && $tab[1][1] == $jug && $tab[2][1] !== null && $tab[2][2] == null && $tab[3][3] == $jug, 'col' => 2],
			['bool' => $tab[0][0] == $jug && $tab[1][1] == $jug && $tab[2][2] == $jug && $tab[3][2] !== null && $tab[3][3] == null, 'col' => 3],

			['bool' => $tab[0][2] != null && $tab[0][3] == null && $tab[1][2] == $jug && $tab[2][1] == $jug && $tab[3][0] == $jug, 'col' => 0],
			['bool' => $tab[0][3] == $jug && $tab[1][1] !== null && $tab[1][2] == null && $tab[2][1] == $jug && $tab[3][0] == $jug, 'col' => 1],
			['bool' => $tab[0][3] == $jug && $tab[1][2] == $jug && $tab[2][0] !== null && $tab[2][1] == null && $tab[3][0] == $jug, 'col' => 2],
			['bool' => $tab[0][3] == $jug && $tab[1][2] == $jug && $tab[2][1] == $jug && $tab[3][0] == null, 'col' => 3],
		];

		for ($i = 0; $i < 4; $i++) {
			// Vertical 4
			$checks[] = ['bool' => $tab[$i][0] == $jug && $tab[$i][1] == $jug && $tab[$i][2] == $jug && $tab[$i][3] == null, 'col' => $i];
			
			// Horizontal 4 0->3 - 3->0
			if ($i == 0) {
				$checks[] = ['bool' => $tab[0][0] == null && $tab[1][0] == $jug && $tab[2][0] == $jug && $tab[3][0] == $jug, 'col' => 0];
				$checks[] = ['bool' => $tab[0][0] == $jug && $tab[1][0] == null && $tab[2][0] == $jug && $tab[3][0] == $jug, 'col' => 1];
				$checks[] = ['bool' => $tab[0][0] == $jug && $tab[1][0] == $jug && $tab[2][0] == null && $tab[3][0] == $jug, 'col' => 2];
				$checks[] = ['bool' => $tab[0][0] == $jug && $tab[1][0] == $jug && $tab[2][0] == $jug && $tab[3][0] == null, 'col' => 3];
			} else {
				$checks[] = ['bool' => $tab[0][$i - 1] != null && $tab[0][$i] == null && $tab[1][$i] == $jug && $tab[2][$i] == $jug && $tab[3][$i] == $jug, 'col' => 0];
				$checks[] = ['bool' => $tab[0][$i] == $jug && $tab[0][$i - 1] != null && $tab[1][$i] == null && $tab[2][$i] == $jug && $tab[3][$i] == $jug, 'col' => 1];
				$checks[] = ['bool' => $tab[0][$i] == $jug && $tab[1][$i] == $jug && $tab[2][$i - 1] != null && $tab[2][$i] == null && $tab[3][$i] == $jug, 'col' => 2];
				$checks[] = ['bool' => $tab[0][$i] == $jug && $tab[1][$i] == $jug && $tab[2][$i] == $jug && $tab[3][$i - 1] != null && $tab[3][$i] == null, 'col' => 3];
			}
		}

		shuffle($checks);

		foreach($checks as $check){
			if($check['bool']) return $check['col'];
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

	private function getColumnaMasUsada(array $id_cols, $max = true)
	{
		$contadas = array_count_values($id_cols);

		$value = $max ? max($contadas) : min($contadas);
		return array_search($value, $contadas);
	}

	private function getEstrategiaAutomatica(string $jug = 'M')
	{
		for ($i = 0; $i < 3; $i++) {
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

		// Diagonal 0/5-10/15
		if ($this->tablero[0][0] == null  && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug) return 0;
		if ($this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][1] !== null && $this->tablero[2][2] == null) return 2;
		//if ($this->tablero[3][3] == $jug && $this->tablero[2][2] == $jug && ($this->tablero[1][0] !== null && $this->tablero[1][1] == null)) return 1;
		//if (($this->tablero[3][2] != null && $this->tablero[3][3] == null) && $this->tablero[2][2] == $jug && $this->tablero[1][1] == $jug) return 3;

		// Diagonal 3/6-9/12
		//if ($this->tablero[0][3] == null  && $this->tablero[1][2] == $jug && $this->tablero[2][1] == $jug) return 0;
		//if ($this->tablero[0][3] == $jug && $this->tablero[1][2] == $jug && $this->tablero[2][0] !== null && $this->tablero[2][1] == null) return 2;
		if ($this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && ($this->tablero[1][1] !== null && $this->tablero[1][2] == null)) return 1;
		if ($this->tablero[3][0] == null && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug) return 3;

		for ($i = 0; $i < 4; $i++) {

			// Vertical 3
			if ($this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == null) return $i;
		}
		for ($i = 0; $i < 3; $i++) {
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

	public function getEstrategiaBorracho(string $jug)
	{
		$id_col = $this->get4enRaya($jug);

		if ($id_col === false) $id_col = $this->get3enRaya($jug);

		if ($id_col === false) $id_col = $this->get2enRaya($jug);

		if ($id_col === false) $id_col = $this->elegirColumnaAleatoria();

		return $id_col;
	}

	public function getEstrategiaCalculada(string $jugador = 'M', int $dificultad = 0)
	{
		$memoria = [];
		//$memoria = $this->memory ?? [];
		$brain = new Brain();
		$brain->setMemory($memoria);
		$casillas = count(array_filter($this->getTableroMerged()));

		$poner = [];
		$evitar = [];
		$repeticiones = $casillas == 0 ? $dificultad : 10;
		$buscando = true;
		while ($buscando) {
			$ganador = false;
			$turno_maq = $jugador == 'M' ? true : false;
			$brain->setTablero($this->tablero);

			$aux = ['num_fin' => 16 - $casillas];
			$partida = [];
			for ($i = $casillas; $i < 16; $i++) {
				$jug = $turno_maq ? 'M' : 'H';
				$dif = $turno_maq ? $dificultad : 1;

				$tokenElegido = false;

				$exceps = [];
				while ($tokenElegido === false) {

					// $id_col = $brain->elegirColumna($jug, $dif);
					$id_col = false;
					if ($dif > 1) {

						$nexts = $brain->getNextsByMemory($jug, $this->memory);
	
						if (count($nexts) > 0) {
				
							$cols = $dificultad == 3 ? array_shift($nexts) : array_pop($nexts);
				
							$id_col = $cols[array_rand($cols)];
						}
					}
					if ($id_col === false || in_array($id_col, $exceps)) $id_col = $dif > 0 ? $brain->getEstrategiaFiestero($jug) : $brain->getEstrategiaBorracho($jug);
					

					if ($turno_maq && !in_array($id_col, $exceps) && $brain->isFutureMemoryMove($id_col)) {
						$exceps[] = $id_col;
						continue;
					}

					if (in_array($id_col, $exceps)) $id_col = $brain->elegirColumnaAleatoria($exceps);

					$tokenElegido = $brain->anadirTokenAColumna($id_col, $jug);

					if ($tokenElegido === false && !in_array($id_col, $exceps)) $exceps[] = $id_col;
				}

				if ($i == $casillas) $aux['id_col'] = $id_col;

				$partida[] = $brain->getTablero();

				if ($brain->elegirGanador($jug)) {
					$ganador = $jug;
					$aux['num_fin'] = ($i - $casillas) + 1;
					break;
				}

				$turno_maq = !$turno_maq;
			}

			if ($ganador == $jugador) {
				$poner[] = $aux;
			} elseif ($ganador !== false) {
				if (!in_array($aux, $evitar)) $evitar[] = $aux;
			} else {
				$aux['num_fin'] = 17;
				if (!in_array($aux, $evitar)) $evitar[] = $aux;
			}

			$partida = $brain->transformPartidaToMemory($partida);
			if (!in_array($partida, $memoria)) {
				$memoria[] = $partida;
				$brain->setMemory($memoria);
				continue;
			} else {
				if ($repeticiones > 0) $repeticiones--;
				if (!$repeticiones) $buscando = false;
			}
		}

		$data = $brain->procesaCalculoData($poner);
		if ($data['num_fin'] != 1) {
			$brain->setTablero($this->tablero);
			$id_col_mortal = $brain->get4enRaya($jugador == 'M' ? 'H' : 'M');
			if ($id_col_mortal !== false) {
				$evitar[] = ['num_fin' => 1, 'id_col' => $id_col_mortal];
			}
		}

		return [
			'data' => $data,
			'datako' => $brain->procesaCalculoData($evitar),
			'mem' => count($memoria)
		];
	}

	public function getEstrategiaFiestero(string $jug)
	{
		$id_col = $this->get4enRaya($jug);

		if ($id_col === false) {
			$id_col_trampa = $this->get4enRayaTrampa($jug);
			if ($id_col_trampa !== false) return $this->elegirColumnaAleatoria([$id_col_trampa]);
		}

		if ($id_col === false) $id_col = $this->get4enRaya($jug == 'M' ? 'H' : 'M');

		if ($id_col === false) $id_col = $this->get3enRaya($jug);

		if ($id_col === false) {
			$excep = [];
			$id_col_mortal = $this->get4enRayaTrampa($jug == 'M' ? 'H' : 'M');
			if ($id_col_mortal !== false) $excep[] = $id_col_mortal;

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

	public function getFuturoToken(int $id_col_futura)
	{
		foreach ($this->tablero[$id_col_futura] as $fila_id => $fila_valor) {
			if (!$fila_valor) return (4 * $id_col_futura) + ($fila_id);
		}

		return false;
	}

	public function getLineaGanadora($tablero, string $jug = 'M'): array
	{
		$triunfos = [
			[0, 1, 2, 3],	[4, 5, 6, 7], [8, 9, 10, 11], [12, 13, 14, 15],
			[0, 4, 8, 12],	[1, 5, 9, 13], [2, 6, 10, 14], [3, 7, 11, 15],
			[0, 5, 10, 15],	[12, 9, 6, 3]
		];

		foreach ($triunfos as $triunfo) {
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

	public function getMemory()
	{
		return $this->memory;
	}

	private function getNextsByMemory(string $jug = 'M', $out_mem = null)
	{
		$nexts = [];
		$plays = $out_mem ?? $this->memory;
		// $plays = json_decode(@file_get_contents(MEM_FILE));

		if ($plays) {
			$tablero = $this->transformTableroToMemory($this->tablero, $jug);
			// $contra = $jug == 'M' ? 'H' : 'M';
			// $tablero = array_map(function ($cell) use ($jug, $contra) {
			// 	if ($cell == $contra) $cell = 2;
			// 	else if ($cell == $jug) $cell = 1;
			// 	else $cell = '';
			// 	return $cell;
			// }, array_merge(...$this->tablero));

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

	public function getTablero()
	{
		return $this->tablero;
	}

	public function getTableroMerged()
	{
		return array_merge(...$this->tablero);
	}

	public function isFutureMemoryMove(int $id_col_futura, string $jugador = 'M')
	{
		$token = $this->getFuturoToken($id_col_futura);
		if ($token !== false) {
			$tablero = $this->transformTableroToMemory($this->tablero, $jugador);
			$t_futuro = $tablero;
			$t_futuro[$token] = 1;
			// $tablero[$token] = $jugador = 'M' ? 1 : 2;
			//return([$tablero, $t_futuro]);
			foreach ($this->memory as $i => $play) {
				// if (in_array($tablero, $play)) return true;
				foreach($play as $i => $move) {
					if ($i == 0 ) { 
						if ($move == $t_futuro) return true; else continue;
					} 
					
					if ($play[$i -1] == $tablero && $move == $t_futuro) return true;
				}
			}
		}
		return false;
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

	public function orderArrayByKey(array $array, string $key = "", bool $asc = true)
	{
		usort($array, function ($a, $b) use ($key, $asc) {
			if (!$asc) {
				$aux = $a;
				$a = $b;
				$b = $aux;
			}

			if (!is_array($a) || !is_array($b)) {
				if ($a == $b) return 0;

				return $a < $b ? -1 : 1;
			}

			if (!isset($a[$key]) || !isset($b[$key])) return 0;

			if ($a[$key] == $b[$key]) return 0;

			return ($a[$key] < $b[$key]) ? -1 : 1;
		});

		return $array;
	}

	public function procesaCalculoData(array $arr)
	{
		$arr = $this->orderArrayByKey($arr, 'num_fin');
		$data = ['num_fin' => 17, 'id_cols' => []];
		if (count($arr) > 0) {
			$data['num_fin'] = $arr[0]['num_fin'];
			foreach ($arr as $item) {

				if ($item['num_fin'] != $arr[0]['num_fin']) continue;

				if (isset($item['id_col']) && !in_array($item['id_col'], $data['id_cols'])) $data['id_cols'][] = $item['id_col'];
			}
		}
		return $data;
	}

	public function setMaxtokens(int $max)
	{
		$this->max_tokens = $max;
	}

	public function setMemory(?array $memory)
	{
		$this->memory = $memory;
	}

	public function setTablero(array $tablero)
	{
		$this->tablero = $tablero;
	}

	private function transformPartidaToMemory($partida, string $jugador = 'M')
	{
		$convert = [];
		foreach ($partida as $move) {
			$convert[] = $this->transformTableroToMemory($move, $jugador);
		}
		return $convert;
	}

	private function transformTableroToMemory($tablero, string $jugador = 'M')
	{
		$convert = [];
		foreach ($tablero as $id_col => $col) {
			foreach ($col as $id_alt => $token) {
				if ($token == $jugador) $convert[] = 1;
				elseif ($token == null) $convert[] = "";
				else $convert[] = 2;
			}
		}
		return $convert;
	}
}
