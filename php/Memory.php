<?php

namespace CuatroPhp\php;

class Memory {

  public function deleteTempPlay($temp_file)
	{
		return unlink(BASE_TEMP . $temp_file);
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
  /*
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
  */

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

			if (!in_array($last, $salida)) $salida[] = $last;
		}

		//$salida = array_filter($salida, function($a, $b) { return $a != $b; });
		return $salida;
	}

  public function getMemory(): array
  {
    $datos = json_decode(@file_get_contents(MEM_FILE));

    if(!$datos) $datos = [];

    return $datos;
  }


	public function getTempPlay(string $temp_file)
	{
		$datos = json_decode(@file_get_contents(BASE_TEMP . $temp_file));
		if (!$datos) $datos = [];

		return $datos;
	}

	public function guardarAmistadBender(string $nombre, string $ganador, int $dificultad)
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

		$amiga->actualizarNumeros($dificultad, $ganador);

		if ($ind !== false) $datos[$ind] = $amiga->toArray();
		else $datos[] = $amiga->toArray();

		return file_put_contents(AMIGAS_FILE, json_encode($datos));
	}

	public function guardarMovimiento(array $tablero, string $temp_file): int
	{
		//$tablero = array_merge(...$this->tablero);

		if (count(array_filter($tablero)) == 1) {
			file_put_contents(BASE_TEMP . $temp_file, '');
			// file_put_contents(MEM_TEMP_FILE, '');
			$datos = [];
		} else {
			$str_datos = @file_get_contents(BASE_TEMP . $temp_file);
			// $str_datos = @file_get_contents(MEM_TEMP_FILE);
			$datos = json_decode($str_datos);
		}

		if (!$datos) $datos = [];

		$datos[] = $tablero;

		return file_put_contents(BASE_TEMP . $temp_file, json_encode($datos));
	}

	public function guardarPartida(array $partida, string $ganador = 'M'): int
	// public function guardarPartida(array $partida, int $ind_ganador): int
	{
    $perdedor = $ganador == 'M' ? 'H' : 'M';

		if ($partida && count($partida) > 0) {
			$datos = json_decode(@file_get_contents(MEM_FILE));
			if (!$datos) $datos = [];

			usort($datos, function ($a, $b) {
				if (count($a) == count($b)) return 0;
				
        return (count($a) < count($b)) ? -1 : 1;
			});

			// $ganador = $ind_ganador % 2 == 0 ? 'H' : 'M';
			// $perdedor = $ganador == 'H' ? 'M' : 'H';

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
}