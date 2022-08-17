<?php

namespace CuatroPhp\php;

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