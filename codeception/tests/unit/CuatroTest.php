<?php
require_once '../php/cuatro.php';

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

    file_put_contents(MEM_FILE, json_encode([$this->mem_play_v]));
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
    $res = $this->cuatro->echarFicha($tablero, 1, 0);

    // Visible con opcion -vv o --debug
    // $this->tester->seeMyVar($res);
    // codecept_debug($res);

    $this->assertEquals(16, count($res['tablero']), 'Tablero debería tener 16 elementos');
    $this->assertEquals('H', $res['tablero'][3], 'Tablero[3] debería ser H');

    $this->assertEquals('Has colocado ficha en la columna 1', $res['mensaje'][0], 'Mensaje[0] debería ser correcto');
    $this->assertEquals('No hay más posiciones disponibles.', $res['mensaje'][1], 'Mensaje[1] debería ser correcto');
    $this->assertEquals('La partida termina en tablas', $res['mensaje'][2], 'Mensaje[2] debería ser correcto');
  }

  public function testElegirColumnaAleatoria_Ok()
  {
    $this->cuatro->tablero = [['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']];
    
    $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAleatoria', []);

    $this->assertContains($res, [0, 1], "Res debería ser 0 o 1");
  }
  
  public function testElegirColumnaAleatoria_OkExceps()
  {
    $this->cuatro->tablero = [['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']];
        
    $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAleatoria', [[1]]);

    $this->assertSame(0, $res, "Res debería ser 0 por excep");
        
    $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAleatoria', [[0, 1]]);

    //$this->tester->seeMyVar($res);
    $this->assertContains($res, [0, 1], "Res debería ser 0 o 1 por excep");
  }
  
  /**
   * @group wip
   */
  public function testElegirColumnaAleatoria_OkExceps4()
  {
    $this->cuatro->tablero = [['M', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', null], ['H', 'M', 'M', null]];
        
    $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAleatoria', [[0, 1, 2, 3]]);

    $this->assertContains($res, [0, 1, 2, 3], "Res debería ser algo con exceps completo");
    // $this->assertSame(0, $res, "Res debería ser 0 por excep");
        
    // $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAleatoria', [[0, 1]]);

    //$this->tester->seeMyVar($res);
  }

  public function testElegirColumnaAleatoria_Ko()
  {
    $this->cuatro->tablero = [['M', 'M', 'M', 'H'], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H'], ['H', 'M', 'M', 'H']];
        
    $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAleatoria', [[]]);

    $this->assertSame(false, $res, "Res debería ser false por no tener lugar");
  }

  /**
   * @group wip
   */
  public function testElegirColumnaAprendizaje()
  {
    $this->cuatro->tablero = [['M', 'M', null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAprendizaje', []);
    $this->tester->seeMyVar($res);

    $this->assertNotSame(3, $res, "Res no debería ser 3 integer por movimiento registrado");

    $this->assertContains($res, [0, 1, 2], "Res debería ser 0 o 1 o 2");
    
    // $res = $this->tester->callMethod($this->cuatro, 'elegirColumnaAprendizaje', ['H']);

    // $this->assertSame(0, $res, "Res debería ser 0 por excep");
  }

  /**
   * group wip
   */
  public function testGetLosesByMemory()
  {
    $this->cuatro->tablero = [['M', 'M', null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'getLosesByMemory', ['H']);
    // $this->tester->seeMyVar($res);

    $this->assertSame([3], $res, "Res debería ser [3] por movimiento registrado");
    
    $this->cuatro->tablero = [['M', 'M', 'M', null], ['H', null, null, null], [null, null, null, null], ['H', null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'getLosesByMemory', []);

    $this->assertSame([1], $res, "Res debería ser [1] por siguiente movimiento registrado");
  }

  public function testGetEstrategiaResacoso_Ok()
  {
    $this->cuatro->tablero = [['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'getEstrategiaResacoso', ['M']);

    $this->assertSame(0, $res, "Res debería ser 0 integer por próximo movimiento");
  }

  public function testGetEstrategiaResacoso_OkMenosPasos()
  {
    $fake = [
      [1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], [1, '', '', '', 2, '', '', '', '', '', '', '', '', '', '', ''],
      [1, '', '', '', 2, 1, '', '', '', '', '', '', '', '', '', ''], [1, 1, '', '', 2, '', '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, '', 2, '', '', '', '', '', '', '', 2, '', '', '']
    ];
    file_put_contents(MEM_FILE, json_encode([$this->mem_play_v, $fake]));

    $this->cuatro->tablero = [['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'getEstrategiaResacoso', ['M']);
    $this->assertSame(0, $res, "Res debería ser 0 integer por dificultad 0 - mas movimientos");
    
    $this->cuatro->dificultad = 3;
    $res = $this->tester->callMethod($this->cuatro, 'getEstrategiaResacoso', ['M']);

    $this->assertSame(1, $res, "Res debería ser 1 integer por dificultad 3 - menos movimientos");
  }

  public function testGetEstrategiaResacoso_false()
  {
    $this->cuatro->tablero = [['H', null, null, null], ['M', null, null, null], [null, null, null, null], [null, null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'getEstrategiaResacoso', ['M']);

    $this->assertFalse($res, "Res debería ser false por no tener este movimiento");
  }

  public function testGetEstrategiaResacoso_false_memoria_vacia()
  {
    file_put_contents(MEM_FILE, "");

    $this->cuatro->tablero = [['M', null, null, null], ['H', null, null, null], [null, null, null, null], [null, null, null, null]];
    
    $res = $this->tester->callMethod($this->cuatro, 'getEstrategiaResacoso', ['M']);

    $this->assertFalse($res, "Res debería ser false por memoria vacia");
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
