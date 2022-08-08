<?php

class JuegoCest
{
  public function _before(ApiTester $I)
  {
    $this->datos = $I->getDatosTest();
    //$I->printVar($this->datos);
    $this->prefijoApi = $this->datos['base_url'] . 'php/api.php';
  }

  /**
   * @group wip
   */
  public function postJuegoSolitario(ApiTester $I)
  {
    $I->sendPost($this->prefijoApi, ['accion' => 'juego_solitario', 'num_rounds' => 2]);
    
    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $res = json_decode($I->grabResponse());
    $I->assertObjectHasAttribute('tablero', $res, 'Res debería tener atributo tops');
    $I->assertObjectHasAttribute('mensaje', $res, 'Res debería tener atributo records');
  }
}
