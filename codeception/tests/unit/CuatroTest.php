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
  }

  protected function _after()
  {
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
}
