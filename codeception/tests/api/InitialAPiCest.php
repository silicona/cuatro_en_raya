<?php

class InitialAPiCest
{
  public function _before(ApiTester $I)
  {
    
  }

  public function getKoSinAccion(ApiTester $I)
  {
    $I->sendGet('/');
    $I->seeResponseCodeIsSuccessful();
    $I->seeResponseIsJson();

    $res = json_decode($I->grabResponse(), true);

    $I->assertJar($res['mensaje'], 'Acción no reconocida', 'Mensaje de error');
  }

  public function tryToTest(ApiTester $I)
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
