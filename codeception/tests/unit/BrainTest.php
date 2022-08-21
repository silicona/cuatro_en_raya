<?php

use CuatroPhp\php\Brain;

class BrainTest extends \Codeception\Test\Unit
{
  /**
   * @var \UnitTester
   */
  protected $tester;

  protected function _before()
  {
    $this->brain = new Brain();

    $this->mem_play_v = [ // Vertical 0-0->0-4 - Ganador: 1
      [1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], [1, '', '', '', 2, '', '', '', '', '', '', '', '', '', '', ''],
      [1, 1, '', '', 2, '', '', '', '', '', '', '', '', '', '', ''], [1, 1, '', '', 2, '', '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, '', 2, '', '', '', '', '', '', '', 2, '', '', ''], [1, 1, 1, '', 2, 2, '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, 1, 2, 2, '', '', '', '', '', '', 2, '', '', '']
    ];

    // $this->brain_mem_play_v = [ // Vertical 0-0->0-4 - Ganador: 1
    //   ['M', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null], ['M', null, null, null, 'H', null, null, null, null, null, null, null, null, null, null, null],
    //   ['M', 'M', null, null, 'H', null, null, null, null, null, null, null, null, null, null, null], ['M', 'M', null, null, 'H', null, null, null, null, null, null, null, 'H', null, null, null],
    //   ['M', 'M', 'M', null, 'H', null, null, null, null, null, null, null, 'H', null, null, null], ['M', 'M', 'M', null, 'H', 'H', null, null, null, null, null, null, 'H', null, null, null],
    //   ['M', 'M', 'M', 'M', 'H', 'H', null, null, null, null, null, null, 'H', null, null, null]
    // ];

    $this->brain->setMemory([$this->mem_play_v]);
    file_put_contents(MEM_FILE, json_encode($this->brain->getMemory()));
  }

  /**
   * group wip
   */
  public function testAnadirTokenAColumna()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']]);

    $res = $this->brain->anadirTokenAColumna(0, 'M');

    $this->assertSame(3, $res, "Res debería ser token 3");
  }

  public function testAnadirTokenAColumna_Ko()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']]);

    $res = $this->brain->anadirTokenAColumna(false, 'M');

    $this->assertFalse($res, "Res debería ser false por id_col false");

    $this->brain->setTablero([['M', 'M', 'M', 'H'], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']]);

    $res = $this->brain->anadirTokenAColumna(0, 'M');

    $this->assertFalse($res, "Res debería ser false por falta de espacio en columna");
  }

  public function testElegirColumnaAleatoria_Ok()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']]);

    $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', []);

    $this->assertContains($res, [0, 1], "Res debería ser 0 o 1");
  }

  public function testElegirColumnaAleatoria_OkExceps()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']]);

    $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[1]]);

    $this->assertSame(0, $res, "Res debería ser 0 por excep");

    $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[0, 1]]);

    //$this->tester->seeMyVar($res);
    $this->assertContains($res, [0, 1], "Res debería ser 0 o 1 por excep");
  }

  public function testElegirColumnaAleatoria_OkExceps4()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', null]]);

    $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[0, 1, 2, 3]]);

    $this->assertContains($res, [0, 1, 2, 3], "Res debería ser algo con exceps completo");
    // $this->assertSame(0, $res, "Res debería ser 0 por excep");

    // $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[0, 1]]);

    //$this->tester->seeMyVar($res);
  }

  public function testElegirColumnaAleatoria_Ko()
  {
    $this->brain->setTablero([['M', 'M', 'M', 'H'], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']]);

    $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[]]);

    $this->assertSame(false, $res, "Res debería ser false por no tener lugar");
  }
  /**
   * group wip
   */
  public function testElegirColumnaPorCalculo_Ok()
  {
    //$this->brain->setTablero([[null, null, null, null], [null, null, null, null], [null, null, null, null], [null, null, null, null]]);

    //$res = $this->brain->elegirColumnaPorCalculo('M', 3);
    // $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[]]);

    //$this->assertContains($res, [0, 1, 2, 3], "Res debería ser cualquiera válido");

    $this->brain->setTablero([['M', 'M', 'M', 'H'], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H']]);

    $res = $this->brain->elegirColumnaPorCalculo('M', 3);
    // $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[]]);

    $this->assertSame(2, $res['id_col'], "Res debería ser 2 por no único lugar");
  }

  public function testElegirColumnaPorCalculo_KoSinPosiciones()
  {
    $this->brain->setTablero([array('H', 'M', 'H', 'H'), ['H', 'M', 'M', 'M'], array('H', 'M', 'H', 'H'), array('M', 'H', 'M', 'H')]);

    $res = $this->brain->elegirColumnaPorCalculo('M', 3);
    //$this->tester->seeMyVar($res);
    // $res = $this->tester->callMethod($this->brain, 'elegirColumnaAleatoria', [[]]);

    $this->assertFalse($res['ok'], "Res[ok] debería ser false por falta de posiciones");
    $this->assertFalse($res['id_col'], "Res[id_col] debería ser false por falta de posiciones");
  }

  public function testElegirColumnaAprendizaje()
  {
    $this->brain->setTablero([['M', 'M', null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'elegirColumnaAprendizaje', []);
    $this->tester->seeMyVar($res);

    $this->assertNotSame(3, $res, "Res no debería ser 3 integer por movimiento registrado");

    $this->assertContains($res, [0, 1, 2], "Res debería ser 0 o 1 o 2");

    // $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAprendizaje', ['H']);

    // $this->assertSame(0, $res, "Res debería ser 0 por excep");
  }

  public function testGet3EnRaya_Ok()
  {
    $this->brain->setTablero([['M', 'M', null, null], ['H', 'H', 'H', null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'get3enRaya', ['M']);

    $this->assertSame(0, $res, "Res debería ser 0 integer por próximo movimiento");
  }

  public function testGetEstrategiaBorracho_Ok()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'H', 'H', null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getEstrategiaBorracho', ['M']);

    $this->assertSame(0, $res, "Res debería ser 0 integer por próximo movimiento");
  }

  /**
   * group wip
   */
  public function testGetEstrategiaCalculada_Ok()
  {
    // $this->brain->setTablero([[null, null, null, null], [null, null, null, null], [null, null, null, null], [null, null, null, null]]);
    $this->brain->setTablero([['M', 'M', null, null], ['H', null, null, null], ['H', null, null, null], [null, null, null, null]]);

    $this->brain->setTablero([['M', 'H', null, null], ['M', 'H', null, null], ['M', 'H', null, null], [null, null, null, null]]);
    $res = $this->brain->getEstrategiaCalculada('M', 3);
    //$this->tester->seeMyVar($res['mem']);

    $this->assertSame(1, $res['data']['num_fin'], "res['data'][num_fin] debería ser 1 por proxima movimiento");

    $this->assertSame(3, $res['data']['id_cols'][0], "res['data']['id_cols'][0] debería ser 3 por proxima victoria");

    $this->brain->setTablero([['M', 'H', 'M', 'H'], ['M', 'H', 'M', 'M'], ['M', 'H', 'H', null], ['H', 'M', 'M', 'M']]);
    $res = $this->brain->getEstrategiaCalculada('M', 3);

    $this->assertSame(17, $res['data']['num_fin'], "res['data'][num_fin] debería ser 17 por ausencia de victoria");

    $this->assertSame(2, $res['datako']['id_cols'][0], "res['datako']['id_cols'][0] debería ser 2 para evitar tablas");
  }

  public function testGetEstrategiaCalculada_OkEvitaPerder()
  {
    $this->brain->setTablero([['M', 'H', 'M', null], ['H', 'H', null, null], ['M', 'H', 'M', null], ['H', null, null, null]]);
    $res = $this->brain->getEstrategiaCalculada('M', 3);
    // $this->tester->seeMyVar($res);

    $this->assertSame(1, $res['datako']['num_fin'], "res['datako'][num_fin] debería ser 1 para evitar victoria del contrincante");

    $this->assertSame(3, $res['datako']['id_cols'][0], "res['datako']['id_cols'][0] debería ser 3 para evitar proxima victoria del contrincante");

    $this->brain->setTablero([['M', 'H', 'M', null], ['H', 'H', null, null], ['M', 'H', null, null], [null, null, null, null]]);
    $res = $this->brain->getEstrategiaCalculada('M', 3);

    $this->assertSame(2, $res['datako']['num_fin'], "res['datako'][num_fin] debería ser 2 para evitar Paso Previo del contrincante");

    $this->assertSame(3, $res['datako']['id_cols'][0], "res['datako']['id_cols'][0] debería ser 3 por proxima victoria por paso previo del contrincante");
  }

  public function testGetEstrategiaCalculada_KoSinPosiciones()
  {
    $this->brain->setTablero([array('H', 'M', 'H', 'H'), ['H', 'M', 'M', 'M'], array('H', 'M', 'H', 'H'), array('M', 'H', 'M', 'H')]);

    $res = $this->brain->getEstrategiaCalculada('M', 3);
    //$this->tester->seeMyVar($res);

    $this->assertSame($res['data'], $res['datako'], "data y datako deberían ser iguales");

    $this->assertSame(17, $res['data']['num_fin'], "res['data'][num_fin] debería ser 1 para evitar victoria del contrincante");

    $this->assertSame(0, count($res['data']['id_cols']), "res['data']['id_cols'] no debería tener elementos");

    // $this->brain->setTablero([['M', 'H', 'M', null], ['H', 'H', null, null], ['M', 'H', null, null], [null, null, null, null]]);
    // $res = $this->brain->getEstrategiaCalculada('M', 3);

    // $this->assertSame(2, $res['datako']['num_fin'], "res['datako'][num_fin] debería ser 2 para evitar Paso Previo del contrincante");

    // $this->assertSame(3, $res['datako']['id_cols'][0], "res['datako']['id_cols'][0] debería ser 3 por proxima victoria por paso previo del contrincante");
  }

  public function testGetEstrategiaFiestero_Ok()
  {
    $this->brain->setTablero([['H', 'M', null, null], ['H', 'M', 'H', null], [null, null, null, null], ['M', 'M', null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getEstrategiaBorracho', ['M']);

    $this->assertNotSame(2, $res, "Res no debería ser 2 por movimiento de trampa");

    $this->assertContains($res, [0, 1, 3], "Res debería ser algo de [0, 1, 3] por movimiento de trampa");
  }

  public function testGetEstrategiaResacoso_Ok()
  {
    $this->brain->setTablero([['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getEstrategiaResacoso', ['M']);

    $this->assertSame(0, $res, "Res debería ser 0 integer por próximo movimiento");
  }

  public function testGetEstrategiaResacoso_OkMenosPasos()
  {
    $fake = [
      [1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], [1, '', '', '', 2, '', '', '', '', '', '', '', '', '', '', ''],
      [1, '', '', '', 2, 1, '', '', '', '', '', '', '', '', '', ''], [1, 1, '', '', 2, '', '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, '', 2, '', '', '', '', '', '', '', 2, '', '', '']
    ];
    $this->brain->setMemory([$this->mem_play_v, $fake]);

    $this->brain->setTablero([['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getEstrategiaResacoso', ['M']);
    $this->assertSame(0, $res, "Res debería ser 0 integer por dificultad 0 - mas movimientos");

    //$this->cuatro->dificultad = 3;
    $res = $this->tester->callMethod($this->brain, 'getEstrategiaResacoso', ['M', 3]);

    $this->assertSame(1, $res, "Res debería ser 1 integer por dificultad 3 - menos movimientos");
  }

  public function testGetEstrategiaResacoso_OkMemoriaVacia()
  {
    file_put_contents(MEM_FILE, "");

    $this->brain->setMemory(json_decode(file_get_contents(MEM_FILE)));

    $this->brain->setTablero([['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getEstrategiaResacoso', ['M']);

    $this->assertEquals('integer', gettype($res), "Res debería ser integer por memoria vacia");
  }

  public function testGetLosesByMemory()
  {
    $this->brain->setTablero([['M', 'M', null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getLosesByMemory', ['H']);
    // $this->tester->seeMyVar($res);

    $this->assertSame([3], $res, "Res debería ser [3] por movimiento registrado");

    $this->brain->setTablero([['M', 'M', 'M', null], ['H', null, null, null], [null, null, null, null], ['H', null, null, null]]);

    $res = $this->tester->callMethod($this->brain, 'getLosesByMemory', []);

    $this->assertSame([1], $res, "Res debería ser [1] por siguiente movimiento registrado");
  }

  /**
   * group wip
   */
  public function testIsFutureMemoryMove()
  {
    $this->brain->setTablero([[null, null, null, null], [null, null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->brain->isFutureMemoryMove(0, 'M');

    $this->assertTrue($res, "Debería ser true por primer movimiento registrado");

    $this->brain->setTablero([['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->brain->isFutureMemoryMove(0, 'M');
    //$this->tester->seeMyVar($res); return;

    $this->assertTrue($res, "Debería ser true por siguiente movimiento registrado");
  }

  public function testIsFutureMemoryMove_Ko()
  {
    $this->brain->setTablero([['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->brain->isFutureMemoryMove(1, 'M');

    $this->assertFalse($res, "Debería ser false por futuro movimiento no registrado");

    $this->brain->setTablero([[null, null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]]);

    $res = $this->brain->isFutureMemoryMove(0, 'M');

    $this->assertFalse($res, "Debería ser false por movimiento pasado no registrado");
  }

  public function testOrderArrayByKey_OkOrdenaPorColumnaIntegerOStringAscYDesc()
  {
    $array = [
      ['id_col' => 0, 'num_fin' => 6],
      ['id_col' => 1, 'num_fin' => 3]
    ];
    $res = $this->brain->orderArrayByKey($array, 'num_fin');

    $this->assertEquals(1, $res[0]['id_col'], '$res[0][id_col] debería ser 1 con num_fin menor integer');

    $array = [
      ['id_col' => 0, 'num_fin' => 3],
      ['id_col' => 1, 'num_fin' => 6]
    ];
    $res = $this->brain->orderArrayByKey($array, 'num_fin', false);

    $this->assertEquals(1, $res[0]['id_col'], '$res[0][id_col] debería ser 1 con num_fin mayor integer');

    $array = [
      ['id_col' => 0, 'num_fin' => "bbb"],
      ['id_col' => 1, 'num_fin' => "aaa"]
    ];
    $res = $this->brain->orderArrayByKey($array, 'num_fin');

    $this->assertEquals(1, $res[0]['id_col'], '$res[0][id_col] debería ser 1 con num_fin string');
  }

  /**
   * group wip
   */
  public function testOrderArrayByKey_OkOrdenaPorIndexAscYDesc()
  {
    $array = [0 => 'bbb', 1 => 'ccc', 2 => 'aaa'];
    $res = $this->brain->orderArrayByKey($array);

    $this->assertEquals('aaa', $res[0], '$res[0] debería ser aaa');

    $res = $this->brain->orderArrayByKey($array, "", false);

    $this->assertEquals('ccc', $res[0], '$res[0] debería ser ccc por order desc');
  }

  public function testOrderArrayByKey_KoNoOrdenaSinColumna()
  {
    $array = [
      ['id_col' => 0, 'num_fin' => 6],
      ['id_col' => 1, 'num_fin' => 3]
    ];
    $res = $this->brain->orderArrayByKey($array, 'columna_erronea');

    $this->assertEquals(0, $res[0]['id_col'], '$res[0][id_col] debería ser 0 por columna erronea');
  }

  public function testOrderArrayByKey_KoArrayVacio()
  {
    $res = $this->brain->orderArrayByKey([]);

    $this->assertEquals([], $res, '$res debería ser []');
  }

  public function testTransformTableroToMemory()
  {
    $tablero = [['M', 'M', null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]];

    $res = $this->tester->callMethod($this->brain, 'transformTableroToMemory', [$tablero, 'M']);

    $this->assertSame(1, $res[0], "res[0] debería ser 1 por M");
    $this->assertSame("", $res[2], "res[2] debería ser String vacio por null");
    $this->assertSame(2, $res[4], "res[4] debería ser 2 por H");
  }
}
