<?php

use CuatroPhp\php\Cuatro;

class CuatroTest extends \Codeception\Test\Unit
{
  /**
   * @var \UnitTester
   */
  protected $tester;

  protected function _before()
  {
    $this->cuatro = new Cuatro;

    $this->mem_play_v = [ // Vertical 0-0->0-4 - Ganador: 1
      [1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], [1, '', '', '', 2, '', '', '', '', '', '', '', '', '', '', ''],
      [1, 1, '', '', 2, '', '', '', '', '', '', '', '', '', '', ''], [1, 1, '', '', 2, '', '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, '', 2, '', '', '', '', '', '', '', 2, '', '', ''], [1, 1, 1, '', 2, 2, '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, 1, 2, 2, '', '', '', '', '', '', 2, '', '', '']
    ];

    $this->cuatro->brain->setMemory([$this->mem_play_v]);
    file_put_contents(MEM_FILE, json_encode($this->cuatro->brain->getMemory()));

    $this->play_file = "play_test.txt";
    file_put_contents($this->tester->params['base_test_file'] . $this->play_file, json_encode([]));
  }

  protected function _after()
  {
  }

  public function testRecibiendoParams()
  {
    $this->assertMatchesRegularExpression('/memoria_test\.txt$/', $this->tester->params['mem_file'], 'Mem_file debería ser correcto');

    $this->assertSame(MEM_FILE, $this->tester->params['mem_file'], 'MEM_FILE debería ser correcto');
  }

  public function testEcharFicha_UltimoMovimientoMaquinaVictoria()
  {
    $memoria_pre = $this->cuatro->mem->getmemory();
    
    $tablero = array_merge(
      array('H', 'M', null, null),
      array('H', 'M', 'M', 'M'),
      array('H', 'M', 'H', 'H'),
      array('M', 'H', 'M', 'H'),
    );
    
    $res = $this->cuatro->echarFicha($tablero, 1, 0, 'Nombre de test', $this->play_file);
    
    $memoria_post = $this->cuatro->mem->getmemory();
    $count_post = count($memoria_post);
    // Visible con opcion -vv o --debug
    //$this->tester->seeMyVar($this->tester->params);
    // codecept_debug($res);
    
    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');
    $this->assertEquals('H', $res['tablero'][2], 'Tablero[2] debería ser H por jugada humana');
    $this->assertEquals('M', $res['tablero'][3], 'Tablero[3] debería ser M por victoria de maquina');
    
    $this->assertEquals('Has colocado ficha en la columna 1', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('Yo he jugado en la columna 1', $res['mensaje'][1], 'Mensaje[1] debería ser correcto');
    $this->assertEquals('He ganado la partida, biológico!!', $res['mensaje'][2], 'Mensaje[2] debería ser correcto');
    
    $this->assertSame(2, $count_post, 'Memoria debería tener dos partidas');
    $this->assertGreaterThan(count($memoria_pre), $count_post, 'Debería haberse guardado una nueva partida');
    
    $partida = $memoria_post[1];
    $mov = implode(',',$partida[0]);
    $this->assertMatchesRegularExpression("/[^HM]+/", $mov, "Debería guardarse como integers");
  }

  public function testEcharFicha_UltimoMovimientoHumano_Tablas()
  {
    $tablero = array_merge(
      array('H', 'M', 'H', null),
      array('H', 'M', 'M', 'M'),
      array('H', 'M', 'H', 'H'),
      array('M', 'H', 'M', 'H'),
    );
    $res = $this->cuatro->echarFicha($tablero, 1, 0, 'Nombre de test', $this->play_file);

    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');
    $this->assertEquals('H', $res['tablero'][3], 'Tablero[3] debería ser H');

    $this->assertEquals('Has colocado ficha en la columna 1', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('No hay más posiciones disponibles.', $res['mensaje'][2], 'Mensaje[1] debería ser correcto');
    $this->assertEquals('La partida termina en tablas', $res['mensaje'][3], 'Mensaje[2] debería ser correcto');
  }

  public function testIniciarJuegoAprendizaje()
  {
    $rounds = 10;
    // $rounds = 5000;
    $res = $this->cuatro->iniciarJuegoAprendizaje($rounds);

    // $this->tester->seeMyVar($res['mensaje']);
    $this->assertEquals("Ejecutadas $rounds partidas en solitario.", $res['mensaje'][3], 'Mensaje[3] debería contar todos los rounds ejecutados');

    $this->assertEquals('¿Le damos caña otra vez?', $res['mensaje'][count($res['mensaje']) - 1], 'Mensaje[last] debería ser correcto');
  }

  public function testGetBenderFriends()
  {
    $res = $this->cuatro->getBenderFriends();

    $this->assertEquals('array', gettype($res['tops']), 'Tops debería estar definido');
  }

  /**
   * group wip
   */
  public function testIniciarJuegoAutomatico()
  {
    $res = $this->cuatro->iniciarJuegoAutomatico();
    $this->tester->seeMyVar($res);
    $this->assertEquals('Partida automática', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('¿Jugamos otra vez?', $res['mensaje'][count($res['mensaje']) - 1], 'Mensaje[last] debería ser correcto');
  }

  public function testIniciarJuegoSolitario()
  {
    $res = $this->cuatro->iniciarJuegoSolitario();

    $this->assertEquals('¿Jugamos otra vez?', $res['mensaje'][count($res['mensaje']) - 1], 'Mensaje[last] debería ser correcto');
  }

  /**
   * group wip
   */
  public function testOrdenandoArrays()
  {
    $arr = [0];
    $arr2 = [0, 1];
    $arr3 = [0, 1, 2];
    $total = [$arr3, $arr2, $arr];

    usort($total, function ($a, $b) {
      if (count($a) == count($b)) {
        return 0;
      }
      return (count($a) < count($b)) ? -1 : 1;
    });

    $this->assertEquals([0], $total[0], 'total[0] debería ser [0] por ordenamiento de usort por count()');

    $arr = [2, 3, 2, 0, 1, 0, 2, 1, 3];
    $contadas = array_count_values($arr);
    $max = max($contadas);
    $id_col = array_search($max, $contadas);
    $this->assertSame(2, $id_col);

    $arr = array_unique($arr);
    sort($arr);
    $this->tester->seeMyVar($arr);

    // $this->assertSame([], array_count_values($arr));
  }


}
