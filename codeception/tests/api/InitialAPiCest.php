<?php

class InitialAPiCest
{
  public function _before(ApiTester $I)
  {
    $this->datos = $I->getDatosTest();
    $this->prefijoApi = $this->datos['base_url'] . 'php/api.php';
  }

  public function postKo(ApiTester $I)
  {
    $I->sendPost($this->prefijoApi.'/');
    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $res = json_decode($I->grabResponse(), true);

    $I->assertJar($res['mensaje'], 'Acción no reconocida', 'Mensaje de error');
  }

  public function postGetBenderFriends(ApiTester $I)
  {
    $I->sendPost($this->prefijoApi.'/', ['accion' => 'get_bender_friends']);
    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $res = json_decode($I->grabResponse());
    $I->assertObjectHasAttribute('tops', $res, 'Res debería tener atributo tops');
    $I->assertObjectHasAttribute('records', $res, 'Res debería tener atributo records');
    $I->assertFalse($res->error, 'Error debería ser false');
  }

  public function postJuegoAutomatico(ApiTester $I)
  {
    $I->sendPost($this->prefijoApi.'/', ['accion' => 'juego_automatico']);
    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $res = json_decode($I->grabResponse());
    $I->assertObjectHasAttribute('tablero', $res, 'Res debería tener atributo tops');
    $I->assertObjectHasAttribute('mensaje', $res, 'Res debería tener atributo records');
  }

  public function _NoReconocidoComoTestPor_(ApiTester $I)
  {
    //$I->seeResponseContains('{"result":"ok"}');
    // $I->amHttpAuthenticated('service_user', '123456');
    // $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
    // $I->sendPost('/users', [
    //   'name' => 'davert', 
    //   'email' => 'davert@codeception.com'
    // ]);
    // $I->seeResponseContains('{"result":"ok"}');
  }
}
