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
    $res = Sockeador::create_socket();
    
    $this -> assertNotNull($res -> socket, 'socket debería ser un resource Socket');
  }
  
}
