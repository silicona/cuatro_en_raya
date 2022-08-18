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

    //$this->brain->memory = json_decode(@file_get_contents(MEM_FILE));

    //if(!$this->brain->memory) {
      $this->brain->setMemory([$this->mem_play_v]);
      file_put_contents(MEM_FILE, json_encode($this->brain->getMemory()));
    //}

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
  
  /**
   * group wip
   */
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

  public function testGetEstrategiaBorracho_Ok()
  {
    $this->brain->setTablero([['M', 'M', 'M', null], ['H', 'H', 'H', null], [null, null, null, null], [null, null, null, null]]);
    
    $res = $this->tester->callMethod($this->brain, 'getEstrategiaBorracho', ['M']);

    $this->assertSame(0, $res, "Res debería ser 0 integer por próximo movimiento");
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
}