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
    $this->mem_play_d = [ // Diagonal 0-0->3-3
      ['', '', '', '', '', '', '', '', '', '', '', '', 2, '', '', ''], [1, '', '', '', '', '', '', '', '', '', '', '', 2, '', '', ''],
      [1, '', '', '', '', '', '', '', '', '', '', '', 2, 2, '', ''], [1, '', '', '', '', '', '', '', '', '', '', '', 2, 2, 1, ''],
      [1, '', '', '', '', '', '', '', 2, '', '', '', 2, 2, 1, ''], [1, '', '', '', '', '', '', '', 2, 1, '', '', 2, 2, 1, ''],
      [1, '', '', '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, ''], [1, 1, '', '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, ''],
      [1, 1, 2, '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, ''], [1, 1, 2, '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, 1],
      [1, 1, 2, 2, 2, '', '', '', 2, 1, '', '', 2, 2, 1, 1], [1, 1, 2, 2, 2, '', '', '', 2, 1, 1, '', 2, 2, 1, 1],
      [1, 1, 2, 2, 2, '', '', '', 2, 1, 1, 2, 2, 2, 1, 1], [1, 1, 2, 2, 2, 1, '', '', 2, 1, 1, 2, 2, 2, 1, 1]
    ];
    $this->mem_play_v = [ // Vertical 0-0->0-4
      [1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], [1, '', '', '', 2, '', '', '', '', '', '', '', '', '', '', ''],
      [1, 1, '', '', 2, '', '', '', '', '', '', '', '', '', '', ''], [1, 1, '', '', 2, '', '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, '', 2, '', '', '', '', '', '', '', 2, '', '', ''], [1, 1, 1, '', 2, 2, '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, 1, 2, 2, '', '', '', '', '', '', 2, '', '', '']
    ];
    // $this->mem_play_d = [ // Diagonal 0-0->3-3
    //   [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0], [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0],
    //   [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 0, 0], [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 1, 0],
    //   [1, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 2, 2, 1, 0], [1, 0, 0, 0, 0, 0, 0, 0, 2, 1, 0, 0, 2, 2, 1, 0],
    //   [1, 0, 0, 0, 2, 0, 0, 0, 2, 1, 0, 0, 2, 2, 1, 0], [1, 1, 0, 0, 2, 0, 0, 0, 2, 1, 0, 0, 2, 2, 1, 0],
    //   [1, 1, 2, 0, 2, 0, 0, 0, 2, 1, 0, 0, 2, 2, 1, 0], [1, 1, 2, 0, 2, 0, 0, 0, 2, 1, 0, 0, 2, 2, 1, 1],
    //   [1, 1, 2, 2, 2, 0, 0, 0, 2, 1, 0, 0, 2, 2, 1, 1], [1, 1, 2, 2, 2, 0, 0, 0, 2, 1, 1, 0, 2, 2, 1, 1],
    //   [1, 1, 2, 2, 2, 0, 0, 0, 2, 1, 1, 2, 2, 2, 1, 1], [1, 1, 2, 2, 2, 1, 0, 0, 2, 1, 1, 2, 2, 2, 1, 1]
    // ];
    // $this->mem_play_v = [ // Vertical 0-0->0-4
    //   [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], [1, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    //   [1, 1, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], [1, 1, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0],
    //   [1, 1, 1, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0], [1, 1, 1, 0, 2, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0],
    //   [1, 1, 1, 1, 2, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0]
    // ];
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

  public function test_array_filter_ok()
  {
    $tablero = array(
      array('H', null, null, null),
      array('M', 'M', null, null),
      array('H', 'H', null, null),
      array('M', null, null, null),
    );

    $res = array_filter($tablero);

    $this->assertSame(4, count($res), 'Res debería tener 4 elementos por arrays interiores');
    $this->assertSame(4, count($res[0]), 'Res[0] debería tener 4 elementos porque array_filter no llega a los interiores');
    $this->assertSame(['H', null, null, null], $res[0], 'Res[0] debería tener nulls porque array_filter no llega a los interiores');

    $res = array_filter(array_merge(...$tablero));

    $this->assertSame(6, count($res), 'Res con merge debería tener 6 elementos H o M');
    $check = ['H', 4 => 'M', 5 => 'M', 8 => 'H', 9 => 'H', 12 => 'M'];
    $this->assertSame($check, $res, 'Res con merge no debería tener nulls');
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


  public function test_determinarColumna_DevuelveIdColumna_IdToken()
  {
    $res = Helper::ejecutarMetodo($this->cuatro, 'determinarColumna', [0]);
    $this->assertEquals($res, 0, 'Res debería ser 0 con id_token: 0');
    
    $res = Helper::ejecutarMetodo($this->cuatro, 'determinarColumna', [3]);
    $this->assertEquals($res, 0, 'Res debería ser 0 con id_token: 3');
    
    $res = Helper::ejecutarMetodo($this->cuatro, 'determinarColumna', [14]);
    $this->assertEquals($res, 3, 'Res debería ser 3 con id_token: 14');
    
    $res = Helper::ejecutarMetodo($this->cuatro, 'determinarColumna', [15]);
    $this->assertEquals($res, 3, 'Res debería ser 3 con id_token: 15');
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
    $res = $this->cuatro->echarFicha($tablero, 1, 0);

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
    $res = $this->cuatro->echarFicha($tablero, 1, 0);

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

  public function test_getBenderFriends_ok()
  {
    $nombre = 'Nombre de test';
    $amiga = new AmigaBender($nombre);
    $amiga->actualizarNumeros(0, 'H');

    $ready = file_put_contents(AMIGAS_FILE, json_encode([$amiga->toArray()]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener 0 bytes guardados');

    $res = $this->cuatro->getBenderFriends();

    $this->assertSame($nombre, $res['records'][0]['nombre']);

    $this->assertSame(1, $res['tops'][$amiga->puntos][0]['victorias'], 'Debería tener 1 victoria');
  }

  public function test_getEstrategiaResacoso_InicioM_ok()
  {
    $ready = file_put_contents(MEM_FILE, json_encode([$this->mem_play_v, $this->mem_play_d]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener mas de 0 bytes guardados');

    //$this->dificultad = 3;
    $this->cuatro->tablero = array(
      array('M', null, null, null),
      array('H', null, null, null),
      array(null, null, null, null),
      array(null, null, null, null),
    );
    
    $res = Helper::ejecutarMetodo($this->cuatro, 'getEstrategiaResacoso', ['M']);
    
    $this->assertSame(0, $res, 'Res debería ser 0 por próximo movimiento de mem_play_v');
  }
  
  public function test_getEstrategiaResacoso_InicioH_ok()
  {
    $ready = file_put_contents(MEM_FILE, json_encode([$this->mem_play_v, $this->mem_play_d]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener mas de 0 bytes guardados');
    
    //$this->dificultad = 3;
    $this->cuatro->tablero = array(
      array('M', null, null, null),
      array(null, null, null, null),
      array(null, null, null, null),
      array('H', 'H', null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'getEstrategiaResacoso', ['M']);

    $this->assertSame(3, $res, 'Res debería ser 3 por próximo movimiento de mem_play_d');
  }

  public function test_getEstrategiaResacoso_InicioM_NoEligeMovesPropios()
  {
    $ready = file_put_contents(MEM_FILE, json_encode([$this->mem_play_v, $this->mem_play_d]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener mas de 0 bytes guardados');

    //$this->dificultad = 3;
    $this->cuatro->tablero = array(
      array('M', 'M', null, null),
      array('H', null, null, null),
      array(null, null, null, null),
      array(null, null, null, null),
    );

    $res = Helper::ejecutarMetodo($this->cuatro, 'getEstrategiaResacoso', ['M']);

    $this->assertSame(false, $res, 'Res debería ser false por próximo movimiento de mem_play_v');
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


  public function test_guardarAmistadBender_ok_GuardaNuevaAmistad()
  {
    $ready = file_put_contents(AMIGAS_FILE, '');
    $this->assertSame(0, $ready, 'Ready debería tener 0 bytes guardados');

    $res = Helper::ejecutarMetodo($this->cuatro, 'guardarAmistadBender', ['Nombre de test', "H"]);

    $this->assertGreaterThan($ready, $res, 'Res debería tener más bytes que ready');

    $check = json_decode(file_get_contents(AMIGAS_FILE));
    $this->assertSame(1, count($check), 'Datos debería tener 1 amigo');
    $this->assertSame('Nombre de test', $check[0]->nombre, 'Nombre debería ser correcto');
  }

  public function test_guardarAmistadBender_ok_ActualizaNuevaAmistad()
  {
    $amiga = new AmigaBender('Nombre de test');
    $amiga->actualizarNumeros(0, 'H');

    $ready = file_put_contents(AMIGAS_FILE, json_encode([$amiga->toArray()]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener 0 bytes guardados');

    $res = Helper::ejecutarMetodo($this->cuatro, 'guardarAmistadBender', ['Nombre de test', "M"]);

    //$this->assertGreaterThan($ready, $res, 'Res debería tener más bytes que ready');

    $check = json_decode(file_get_contents(AMIGAS_FILE));
    $this->assertSame(1, count($check), 'Datos debería tener 1 amigo');
    $this->assertSame('Nombre de test', $check[0]->nombre, 'Nombre debería ser correcto');
    $this->assertSame(1, $check[0]->nums[0]->d, 'Debería tener derrota en dificultad 0');
  }

  public function test_guardarPartida_ok()
  {
    $ready = file_put_contents(MEM_FILE, json_encode([$this->mem_play_d]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener mas de 0 bytes guardados');

    $play = array();
    foreach($this->mem_play_v as $move){
      $arr_move = [];
      foreach($move as $token){
        if ($token == 1) $arr_move[] = 'M';
        else if ($token == 2) $arr_move[] = 'H';
				else $arr_move[] = '';
      }
      $play[] = $arr_move;
    }

    $res = Cuatro::guardarPartida($play, 1);

    $this->assertGreaterThan($ready, $res, 'Res debería tener más bytes que ready');
  }

  public function test_guardarPartida_NoGuardaPartidaDuplicada()
  {
    $ready = file_put_contents(MEM_FILE, json_encode([$this->mem_play_v, $this->mem_play_d]));
    $this->assertGreaterThan(0, $ready, 'Ready debería tener mas de 0 bytes guardados');
    $play = array();
    foreach($this->mem_play_v as $move){
      $arr_move = [];
      foreach($move as $token){
        if ($token == 1) $arr_move[] = 'M';
        else if ($token == 2) $arr_move[] = 'H';
				else $arr_move[] = '';
      }
      $play[] = $arr_move;
    }

    $res = Cuatro::guardarPartida($play, 4, 1);

    $this->assertSame($res, $ready, 'Res debería tener los mismos bytes que ready');
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
