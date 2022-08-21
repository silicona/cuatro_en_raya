<?php

use CuatroPhp\php\Memory;

class MemoryTest extends \Codeception\Test\Unit
{
  /**
   * @var \UnitTester
   */
  protected $tester;

  protected function _before()
  {
    $this->mem = new Memory();

    $this->mem_play_v = [ // Vertical 0-0->0-4 - Ganador: 1
      [1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], [1, '', '', '', 2, '', '', '', '', '', '', '', '', '', '', ''],
      [1, 1, '', '', 2, '', '', '', '', '', '', '', '', '', '', ''], [1, 1, '', '', 2, '', '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, '', 2, '', '', '', '', '', '', '', 2, '', '', ''], [1, 1, 1, '', 2, 2, '', '', '', '', '', '', 2, '', '', ''],
      [1, 1, 1, 1, 2, 2, '', '', '', '', '', '', 2, '', '', '']
    ];

    //$this->brain->memory = json_decode(@file_get_contents(MEM_FILE));

    //if(!$this->brain->memory) {
      //$this->brain->setMemory([$this->mem_play_v]);
      //file_put_contents(MEM_FILE, json_encode($this->brain->getMemory()));
    //}
  }

  /**
   * group wip
   */
  public function testCheckMemory()
  {
    // $memory = file_get_contents($this->tester->params);
    //$this->assertFalse($this->tester->params);
    $mem_file = json_decode(file_get_contents($this->tester->params['base_file'] . "php/memoria_bender.txt"));
    //$this->tester->seeMyVar(count($mem_file));

  }

  public function testCheckPartidaRepetida()
  {
    $datos = json_decode(file_get_contents(BASE_FILE . "php/memoria_bender.txt"));
    if (!$datos) $datos = [];

    usort($datos, function ($a, $b) {
      if (count($a) == count($b)) {
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
      if ($play == $partida) $repetida = true;
    }

    $this->assertFalse($repetida, 'No debería estar repetida');
  }
}