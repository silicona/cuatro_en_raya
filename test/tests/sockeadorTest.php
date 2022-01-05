<?php

declare(strict_types=1);

require_once '../php/class.sockeador.php';

use PHPUnit\Framework\TestCase;

class SockeadorTest extends TestCase
{

  public function setUp(): void
  {
  }

  public function testCrearSocket()
  {
    $res = Sockeador::crear_socket();
    
    $this -> assertNotNull($res -> socket, 'socket debería ser un resource Socket');
  }
  
  public function EnviarAlSocket()
  {
    $res = Sockeador::crear_socket();
    $res -> enviar_al_socket('Jarjajrjajr');
    
    //$res -> recibir_del_socket();
    //$this -> assertNotNull($res -> socket, 'socket debería ser un resource Socket');
  }
}
