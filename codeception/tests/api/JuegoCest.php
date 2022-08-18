<?php

class JuegoCest
{
  public function _before(ApiTester $I)
  {
    $this->datos = $I->getDatosTest();
    //$I->printVar($this->datos);
    $this->url_api = $this->datos['base_url'] . 'php/api.php';
  }

  /**
   * @group wip
   */
  public function postJuegoAutomatico(ApiTester $I)
  {
    $I->sendPost($this->url_api, ['accion' => 'juego_automatico']);

    $res = json_decode($I->grabResponse());
    $I->printVar($res);
    
    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $I->assertObjectHasAttribute('tablero', $res, 'Res debería tener atributo tops');
    $I->assertObjectHasAttribute('mensaje', $res, 'Res debería tener atributo records');
  }

  /**
   * group wip
   */
  public function postJuegoSolitario(ApiTester $I)
  {
    $I->sendPost($this->url_api, ['accion' => 'juego_solitario']);
    
    $res = json_decode($I->grabResponse());
    $I->printVar($res);

    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $I->assertObjectHasAttribute('tablero', $res, 'Res debería tener atributo tops');
    $I->assertObjectHasAttribute('mensaje', $res, 'Res debería tener atributo records');
  }

  /**
   * group wip
   */
  public function postJuegoAprendizaje(ApiTester $I)
  {
    $I->sendPost($this->url_api, ['accion' => 'juego_aprendizaje', 'num_rounds' => 2]);
    
    $res = json_decode($I->grabResponse());
    $I->printVar($res);

    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $I->assertObjectHasAttribute('tablero', $res, 'Res debería tener atributo tops');
    $I->assertObjectHasAttribute('mensaje', $res, 'Res debería tener atributo records');
  }
}
