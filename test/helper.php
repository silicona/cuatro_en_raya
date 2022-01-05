<?php

class Helper
{
  public static function ejecutarMetodo($obj, $nombre, array $args = [])
  {
    $clase = new ReflectionClass($obj);
    $metodo = $clase->getMethod($nombre);
    $metodo->setAccessible(true);

    return $metodo -> invokeArgs($obj, $args);
  }

  public static function getIdToken(int $id_columna, int $altura): int
	{
		$posiciones = array_chunk(range(0, 15), 4);
		return $posiciones[$id_columna][$altura];
	}
}
?>