<?php

declare(strict_types=1);

require_once 'helper.php';
require_once '../php/cuatro.php';

use PHPUnit\Framework\TestCase;

class CuatroTest extends TestCase
{
  public function setUp(): void
  {
    $this->cuatro = new Cuatro();
  }

  public function test_anadirTokenAColumna_ok()
  {
    $this->cuatro->tablero = array(
      array('H', null, null, null),
      array('M', 'M', null, null),
      array('H', 'H', null, null),
      array('M', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'anadirTokenAColumna', [1]);
    $this->assertEquals(6, $res, 'Res debería ser 6 por token insertado en columna 2');

    $res = Helper::ejecutarMetodo($this->cuatro, 'anadirTokenAColumna', [3]);
    $this->assertEquals(13, $res, 'Res debería ser 13 por token insertado en columna 4');
  }

  public function test_anadirTokenAColumna_NoPermiteRebasarColumnaSegunMaxTokens()
  {
    $this->cuatro->tablero = array(
      array('H', null, null, null),
      array('M', 'M', 'H', 'M'),
      array('H', 'H', 'M', null),
      array('M', 'M', 'H', null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'anadirTokenAColumna', [1]);
    $this->assertFalse($res, 'Res debería ser false por columna 2 llena con max_tokens: 4');

    $res = Helper::ejecutarMetodo($this->cuatro, 'anadirTokenAColumna', [2]);
    $this->assertEquals(11, $res, 'Res debería ser 11 por token en columna 3 con max_tokens: 4');

    $this->cuatro->max_tokens = 3;
    $res = Helper::ejecutarMetodo($this->cuatro, 'anadirTokenAColumna', [3]);
    $this->assertFalse($res, 'Res debería ser false por columna 4 llena con max_tokens: 3');
  }

  public function test_anadirTokenAColumna_DevuelveFalse()
  {
    $this->cuatro->tablero = array(
      array('M', 'M', 'H', 'M'),
      array('H', null, null, null),
      array('H', 'H', 'M', null),
      array('M', 'M', 'H', null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'anadirTokenAColumna', [false]);
    $this->assertFalse($res, 'Res debería ser false por columna 2 llena con max_tokens: 4');
  }


  public function test_determinarToken_DevuelveNumToken_IdColumna_Altura()
  {
    $res = Helper::ejecutarMetodo($this->cuatro, 'determinarToken', [0, 0]);

    $this->assertEquals($res, 0, 'Res debería ser 0 con idColumna: 0 y Altura: 0');

    $res = Helper::ejecutarMetodo($this->cuatro, 'determinarToken', [2, 2]);

    $this->assertEquals($res, 10, 'Res debería ser 10 con idColumna: 2 y Altura: 2');
  }


  public function test_echarFicha_PrimerMovimientoHumano()
  {
    $tablero = array_fill(0, 16, "");
    $res = $this->cuatro->echarFicha($tablero, 1);

    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');
    $this->assertEquals('H', $res['tablero'][0], 'Tablero[0] debería ser H');
    $this->assertEquals('Has colocado ficha en la columna 1', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');

    preg_match('/columna (\d)$/', $res['mensaje'][1], $matches);
    $this->assertGreaterThan(0, $matches[1], 'Matches[1] debería tener el número de columna');

    $id_token = Helper::getIdToken($matches[1] - 1, $matches[1] == 1 ? 1 : 0);
    $this->assertEquals('M', $res['tablero'][$id_token], 'Tablero[' . $id_token . '] debería ser M');
    $this->assertTrue(in_array('M', $res['tablero']), 'Tablero debería tener un elemento M');
  }

  public function test_echarFicha_UltimoMovimientoHumano_Tablas()
  {
    $tablero = array_merge(
      array('H', 'M', 'H', null),
      array('H', 'M', 'M', 'M'),
      array('H', 'M', 'H', 'H'),
      array('M', 'H', 'M', 'H'),
    );
    $res = $this->cuatro->echarFicha($tablero, 1);

    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');
    $this->assertEquals('H', $res['tablero'][3], 'Tablero[3] debería ser H');

    $this->assertEquals('Has colocado ficha en la columna 1', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('No hay más posiciones disponibles.', $res['mensaje'][1], 'Mensaje[1] debería ser correcto');
    $this->assertEquals('La partida termina en tablas', $res['mensaje'][2], 'Mensaje[2] debería ser correcto');
  }


  public function test_elegirColumna_Estrategia()
  {
    $this->cuatro->turno_maq = true;
    $this->cuatro->tablero = array(
      array('H', 'M', 'H', null),
      array('H', 'M', 'M', null),
      array('H', null, null, null),
      array('M', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirColumna');

    $this->assertEquals(2, $res, 'Res debería ser 2 por M-M desde 0-1');
  }

  public function test_elegirColumna_EligeColumnaDisponible()
  {
    $this->cuatro->max_tokens = 3;
    $this->cuatro->tablero = array(
      array('H', 'M', 'H', null),
      array('H', 'H', 'M', null),
      array('M', null, null, null),
      array('M', 'M', 'H', null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirColumna', []);
    $this->assertEquals(2, $res, 'Res debería ser 2 por columna disponible para Humano');

    $this->cuatro->turno_maq = true;
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirColumna', []);
    $this->assertEquals(2, $res, 'Res debería ser 2 por columna disponible para Maquina');
  }

  public function test_elegirColumna_SinColumnasDisponibles_Devuelve_false()
  {
    $this->cuatro->max_tokens = 3;
    $this->cuatro->tablero = array(
      array('H', 'M', 'H', null),
      array('H', 'M', 'M', null),
      array('H', 'M', 'H', null),
      array('M', 'H', 'M', null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirColumna', []);
    $this->assertFalse($res, 'Res debería ser false');
  }


  public function test_elegirConEstrategia_3TokenM_Diagonal()
  {
    $this->cuatro->turno_maq = true;
    //$this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][2] == $jug && $this->tablero[3][2] !== null && $this->tablero[3][3] == null
    $this->cuatro->tablero = array(
      array('M', null, null, null),
      array('H', 'M', null, null),
      array('H', 'H', 'M', null),
      array('M', 'M', 'H', null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);

    $this->assertEquals(3, $res, 'Res debería ser 3 por M-M-M Diagonal desde 0-0');

    // $this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][2] == $jug && $this->tablero[0][2] !== null && $this->tablero[0][3] == null
    $this->cuatro->tablero = array(
      array('M', 'H', 'H', null),
      array('H', 'H', 'M', null),
      array('H', 'M', 'M', null),
      array('M', 'M', 'H', null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);

    $this->assertEquals(0, $res, 'Res debería ser 0 por M-M-M Diagonal desde 3-0');
  }

  public function test_elegirConEstrategia_2TokenM_Diagonal()
  {
    $this->cuatro->turno_maq = true;
    //$this->tablero[0][0] == $jug && $this->tablero[1][1] == $jug && $this->tablero[2][1] !== null && $this->tablero[2][2] == null		
    $this->cuatro->tablero = array(
      array('M', null, null, null),
      array('H', 'M', null, null),
      array('H', 'H', null, null),
      array('M', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia');

    $this->assertEquals(2, $res, 'Res debería ser 2 por M-M Diagonal y apoyo H desde 0-0');

    // $this->tablero[3][0] == $jug && $this->tablero[2][1] == $jug && $this->tablero[1][1] !== null && $this->tablero[1][2] == null
    $this->cuatro->tablero = array(
      array('M', null, null, null),
      array('H', 'H', null, null),
      array('H', 'M', null, null),
      array('M', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);

    $this->assertEquals(1, $res, 'Res debería ser 1 por M-M Diagonal y apoyo H desde 3-0');
  }

  public function test_elegirConEstrategia_3TokenM_VH()
  {
    $this->cuatro->turno_maq = true;
    //$this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == $jug && $this->tablero[$i][3] == null
    $this->cuatro->tablero = array(
      array('H', 'H', null, null),
      array('M', 'M', 'M', null),
      array('H', 'H', 'H', null),
      array('M', 'M', null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals(1, $res, 'Res debería ser 1 por M-M-M Vertical desde 1-0 por primacia del bucle');

    //$this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == $jug && $this->tablero[3][$i] == null
    $this->cuatro->tablero = array(
      array('M', 'M', 'H', null),
      array('H', 'M', null, null),
      array('H', 'M', 'H', null),
      array('H', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals(3, $res, 'Res debería ser 3 por M-M-M Horizontal desde 0-1');

    $this->cuatro->tablero = array(
      array('H', 'M', 'H', null),
      array('M', 'M', null, null),
      array('H', 'M', 'H', null),
      array('H', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals(3, $res, 'Res debería ser 3 por M-M-M Horizontal desde 0-1 por primacia de 3 sobre 2');
  }

  public function test_elegirConEstrategia_2TokenM_VH()
  {
    $this->cuatro->turno_maq = true;
    //$this->tablero[$i][0] == $jug && $this->tablero[$i][1] == $jug && $this->tablero[$i][2] == null
    $this->cuatro->tablero = array(
      array('H', null, null, null),
      array('M', 'M', null, null),
      array('H', 'H', null, null),
      array('M', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals(1, $res, 'Res debería ser 1 por M-M Vertical desde 1-0');

    // $this->tablero[0][$i] == $jug && $this->tablero[1][$i] == $jug && $this->tablero[2][$i] == null
    $this->cuatro->tablero = array(
      array('H', 'M', null, null),
      array('H', 'M', null, null),
      array('H', null, null, null),
      array('M', null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals(2, $res, 'Res debería ser 2 por M-M Horizontal, apoyo H desde 0-1 y null al final');

    $this->cuatro->tablero = array(
      array('H', 'M', 'H', null),
      array('M', 'M', null, null),
      array('H', 'M', 'H', null),
      array(null, null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals(1, $res, 'Res debería ser 2 por M-M Vertical desde 1-0 por no tener apoyo para 3M Horizontal de 0-1');
  }

  public function test_elegirConEstrategia_2TokenM_VH_Devuelve_false_si_Horizontal_imposible()
  {
    $this->cuatro->turno_maq = false;
    $this->cuatro->tablero = array(
      array('H', 'M', null, null),
      array('H', 'M', null, null),
      array('H', null, null, null),
      array('M', 'H', null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertFalse($res, 'Res debería ser false por M-M Horizontal imposible en 3-1: H');
  }

  public function test_elegirConEstrategia_DevuelveFalseSiNoHayPosibilidad()
  {
    $this->cuatro->turno_maq = false;
    $this->cuatro->tablero = array(
      array(null, null, null, null),
      array('H', null, null, null),
      array('H', null, null, null),
      array('M', 'M', null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);

    $this->assertEquals($res, false, 'Res debería ser false porque H no puede hacer horizontal');

    $this->cuatro->tablero = array(
      array('M', null, null, null),
      array('H', 'H', 'M', null),
      array('H', null, null, null),
      array('M', 'M', null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirConEstrategia', []);
    $this->assertEquals($res, false, 'Res debería ser false porque H no tiene dos juntas con posibilidad');
  }


  public function test_elegirGanador_Diagonal()
  {
    $this->cuatro->turno_maq = false;
    $this->cuatro->tablero = array(
      array('H', 'M', 'M', 'H'),
      array('M', 'M', 'H', null),
      array('M', 'H', 'M', 'H'),
      array('H', 'M', 'H', 'H'),
    );
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirGanador');
    $this->assertTrue($res, 'Res debería ser true por triunfo H 12-9-6-3');

    $this->cuatro->turno_maq = true;
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirGanador');
    $this->assertFalse($res, 'Res debería ser false por derrota M');
  }

  public function test_elegirGanador_Horizontal()
  {
    $this->cuatro->turno_maq = false;
    $this->cuatro->tablero = array(
      array('H', 'M', 'H', 'M'),
      array('M', 'M', 'H', null),
      array('M', 'H', 'H', null),
      array('H', 'M', 'H', null),
    );
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirGanador');
    $this->assertTrue($res, 'Res debería ser true por triunfo H 2-6-10-14');

    $this->cuatro->turno_maq = true;
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirGanador');
    $this->assertFalse($res, 'Res debería ser false por derrota M');
  }

  public function test_elegirGanador_Vertical()
  {
    $this->cuatro->turno_maq = false;
    $this->cuatro->tablero = array(
      array('H', 'M', 'M', 'H'),
      array('M', 'M', 'H', null),
      array('M', 'M', 'M', 'H'),
      array('H', 'H', 'H', 'H'),
    );
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirGanador');
    $this->assertTrue($res, 'Res debería ser true por triunfo H 12-13-14-15');

    $this->cuatro->turno_maq = true;
    $res = Helper::ejecutarMetodo($this->cuatro, 'elegirGanador');
    $this->assertFalse($res, 'Res debería ser false por derrota M');
  }

  public function test_getLineaGanadora_DevuelveTriunfo()
  {
    $this->cuatro->turno_maq = false;
    $tablero = array_merge(
      array('H', 'M', 'M', 'H'),
      array('M', 'M', 'H', null),
      array('M', 'H', 'M', 'H'),
      array('H', 'M', 'H', 'H'),
    );
    $res = Helper::ejecutarMetodo($this->cuatro, 'getLineaGanadora', [$tablero]);

    $this->assertEquals([12, 9, 6, 3], $res, 'Res debería ser correcto');
  }


  public function test_iniciarJuegoAutomatico()
  {
    $res = $this->cuatro->iniciarJuegoAutomatico();

    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');

    $this->assertEquals('Partida automática', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('¿Jugamos otra vez?', array_pop($res['mensaje']), 'Mensaje[ultimo] debería ser correcto');
  }

  public function test_ConversionTablero()
  {
    $this->cuatro->tablero = array(
      array('H', 'M', 'H', 'M'),
      array('M', 'M', 'H', null),
      array('M', 'H', 'H', null),
      array('H', 'M', 'H', null),
    );
    $res = array_merge(...$this->cuatro->tablero);

    $this->assertEquals(16, count($res), 'Res debería tener 16 elementos');
    $this->assertEquals('H', $res[0], 'Res[0] debería ser H');
    $this->assertNull($res[15], 'Res[15] debería ser null');
  }
}
