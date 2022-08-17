<?php

namespace CuatroPhp\php;

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