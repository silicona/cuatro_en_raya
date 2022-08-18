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

    //$this->cuatro->memory = json_decode(@file_get_contents(MEM_FILE));

    //if(!$this->cuatro->memory) {
      $this->cuatro->brain->setMemory([$this->mem_play_v]);
      file_put_contents(MEM_FILE, json_encode($this->cuatro->brain->getMemory()));
    //}

  }

  protected function _after()
  {
  }

  public function testRecibiendoParams()
  {
    $this->assertMatchesRegularExpression('/memoria_test\.txt$/', $this->tester->params['mem_file'], 'Mem_file debería ser correcto');
    
    $this->assertSame(MEM_FILE, $this->tester->params['mem_file'], 'MEM_FILE debería ser correcto');
  }

  public function test_echarFicha_UltimoMovimientoHumano_Tablas()
  {
    $tablero = array_merge(
      array('H', 'M', 'H', null),
      array('H', 'M', 'M', 'M'),
      array('H', 'M', 'H', 'H'),
      array('M', 'H', 'M', 'H'),
    );
    $res = $this->cuatro->echarFicha($tablero, 1, 0, 'Nombre de test', 'play_test.txt');

    // Visible con opcion -vv o --debug
    //$this->tester->seeMyVar($res);
    // codecept_debug($res);

    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');
    $this->assertEquals('H', $res['tablero'][3], 'Tablero[3] debería ser H');

    $this->assertEquals('Has colocado ficha en la columna 1', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('No hay más posiciones disponibles.', $res['mensaje'][1], 'Mensaje[1] debería ser correcto');
    $this->assertEquals('La partida termina en tablas', $res['mensaje'][2], 'Mensaje[2] debería ser correcto');
  }

  public function testIniciarJuegoAprendizaje()
  {
    $rounds = 10;
    // $rounds = 5000;
    $res = $this->cuatro->iniciarJuegoAprendizaje($rounds);
    
    // $this->tester->seeMyVar($res['mensaje']);
    $this->assertEquals("Ejecutadas $rounds partidas en solitario.", $res['mensaje'][3], 'Mensaje[3] debería contar todos los rounds ejecutados');

    $this->assertEquals('¿Le damos caña otra vez?', $res['mensaje'][count($res['mensaje'])-1], 'Mensaje[last] debería ser correcto');
  }

  /**
   * @group wip
   */
  public function testIniciarJuegoAutomatico()
  {
    $res = $this->cuatro->iniciarJuegoAutomatico();
$this->tester->seeMyVar($res);
    $this->assertEquals('Partida automática', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('¿Jugamos otra vez?', $res['mensaje'][count($res['mensaje'])-1], 'Mensaje[last] debería ser correcto');
  }

  public function testIniciarJuegoSolitario()
  {
    $res = $this->cuatro->iniciarJuegoSolitario();

    $this->assertEquals('¿Jugamos otra vez?', $res['mensaje'][count($res['mensaje'])-1], 'Mensaje[last] debería ser correcto');
  }


  public function testCheckPartidaRepetida()
  {
		$datos = json_decode(file_get_contents(BASE_FILE . "cuatro_php/php/memoria_bender.txt"));
		if (!$datos) $datos = [];

		usort($datos, function($a, $b){
			if(count($a) == count($b)){
				return 0;
			}

			return (count($a) < count($b)) ? -1 : 1;
		});

    $partida = [ // Diagonal 0-0->3-3 - Ganador 1
      ['', '', '', '', '', '', '', '', '', '', '', '', 2, '', '', ''], [1, '', '', '', '', '', '', '', '', '', '', '', 2, '', '', ''],
      [1, '', '', '', '', '', '', '', '', '', '', '', 2, 2, '', ''], [1, '', '', '', '', '', '', '', '', '', '', '', 2, 2, 1, ''],
      [1, '', '', '', '', '', '', '', 2, '', '', '', 2, 2, 1, ''], [1, '', '', '', '', '', '', '', 2, 1, '', '', 2, 2, 1, ''],
      [1, '', '', '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, ''], [1, 1, '', '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, ''],
      [1, 1, 2, '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, ''], [1, 1, 2, '', 2, '', '', '', 2, 1, '', '', 2, 2, 1, 1],
      [1, 1, 2, 2, 2, '', '', '', 2, 1, '', '', 2, 2, 1, 1], [1, 1, 2, 2, 2, '', '', '', 2, 1, 1, '', 2, 2, 1, 1],
      [1, 1, 2, 2, 2, '', '', '', 2, 1, 1, 2, 2, 2, 1, 1], [1, 1, 2, 2, 2, 1, '', 1, 2, 1, 1, 2, 2, 2, 1, 1]
      // modificada linea ya que esta en archivo de origen
      // [1, 1, 2, 2, 2, '', '', '', 2, 1, 1, 2, 2, 2, 1, 1], [1, 1, 2, 2, 2, 1, '', '', 2, 1, 1, 2, 2, 2, 1, 1]
    ];

    $repetida = false;
		foreach ($datos as $play) {
      if($play == $partida) $repetida = true;
		}

    $this->assertFalse($repetida, 'No debería estar repetida');
  }

  public function test_ordenando_arrays()
  {
    $arr = [0];
    $arr2 = [0, 1];
    $arr3 = [0, 1, 2];
    $total = [$arr3, $arr2, $arr];

    $res = usort($total, function($a, $b){
      if(count($a) == count($b)){
        return 0;
      }
      return (count($a) < count($b)) ? -1 : 1;
    });

    $this->assertEquals([0], $total[0], 'total[0] debería ser correcto');

  }
}
