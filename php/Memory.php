<?php

namespace CuatroPhp\php;

class Memory {

  public function deleteTempPlay($temp_file)
	{
		return unlink(BASE_TEMP . $temp_file);
	}

  public function getBenderFriends()
	{
		$datos = json_decode(file_get_contents(AMIGAS_FILE), true);
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
		if (count(array_filter($tablero)) == 1) {
			file_put_contents(BASE_TEMP . $temp_file, '');
			$datos = [];
		} else {
			$str_datos = @file_get_contents(BASE_TEMP . $temp_file);
			$datos = json_decode($str_datos);
		}

		if (!$datos) $datos = [];

		$datos[] = $tablero;

		return file_put_contents(BASE_TEMP . $temp_file, json_encode($datos));
	}

	public function guardarPartida(array $partida, string $ganador = 'M'): int
	{
    $perdedor = $ganador == 'M' ? 'H' : 'M';

		if ($partida && count($partida) > 0) {
			$datos = json_decode(@file_get_contents(MEM_FILE));
			if (!$datos) $datos = [];

			usort($datos, function ($a, $b) {
				if (count($a) == count($b)) return 0;
				
        return (count($a) < count($b)) ? -1 : 1;
			});

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