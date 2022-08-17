<?php

class SocketUser
{
  public $id;
  public $name = '';
  public $color = '';

  public function __construct()
  {
  }

  public function toArray()
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'color' => $this->color,
    ];
  }
}

class SocketPlay
{
  public $on = false;
  public $movs = [];
  public $contador = 0;
  public $cuatro;
  public $player1;
  public $player2;

  public function __construct(int $p1, int $p2)
  {
    $this->player1 = $p1;
    $this->player2 = $p2;
  }
}

class AmigaBender
{
  public $nombre;
  public $puntos;
  public $nums;

  public function __construct(string $nombre)
  {
    $this->nombre = $nombre;
    $this->puntos = 0;
    $this->nums = [
      0 => ['v' => 0, 'd' => 0, 'e' => 0],
      1 => ['v' => 0, 'd' => 0, 'e' => 0],
      2 => ['v' => 0, 'd' => 0, 'e' => 0],
      3 => ['v' => 0, 'd' => 0, 'e' => 0],
    ];
  }

  public static function factory(array $datos): AmigaBender
  {
    $amiga = new AmigaBender($datos['nombre']);
    $amiga->nums = $datos['nums'];
    $amiga->puntos = $datos['puntos'];

    return $amiga;
  }

  public function toArray(): array {
    return [
      'nombre' => $this->nombre,
      'puntos' => $this->puntos,
      'nums' => $this->nums
    ];
  }

  public function actualizarNumeros(int $dificultad, string $ganador)
  {
    $nota = 'e';
    $puntos = 0;
		if($ganador != '') $nota = $ganador == 'H' ? 'v' : 'd';

		$this->nums[$dificultad][$nota]++;

    if($ganador == 'H') {
      foreach($this->nums as $id_dif => $registro){
        if($id_dif == 0) $id_dif++;
        $puntos += $registro['v'] * $id_dif;
      }
      $this->puntos = $puntos;
    }
    return true;
  }

  public function getTopRecord()
  {
    return array(
      'nombre' => $this->nombre,
      'puntos' => $this->puntos,
      'victorias' => array_sum(array_column($this->nums, 'v'))
    );
  }
}
