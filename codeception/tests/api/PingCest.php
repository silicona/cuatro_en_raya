<?php

class PingCest
{
    public function _before(ApiTester $I)
    {
    }

    /**
     * group wip
     */
    public function getPingTest(ApiTester $I)
    {
        $I->sendGet('ping.php');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $res = json_decode($I->grabResponse(), true);
        // $I->printVar("\nClientIp: " . $res['clientIp']);

        $arrIps = ['87.217.241.238'];
        $I->assertEquals($res['clientIp'], '127.0.0.1', 'clientIp debería ser local');
        // var_dump($res['api2']);
        $I->assertEquals($res['api2']['host'], 'apiv2.avirato.com', 'Api2.host debería ser Apiv2');
        $I->assertContains($res['api2']['headers']['x-real-ip'], $arrIps, 'x-real-ip debería ser Nodo algo');
        $I->assertContains($res['api2']['headers']['x-forwarded-for'], $arrIps, 'x-forwarded-for debería ser Nodo algo');
        // $I->assertEquals($res['request'], '127.0.0.1', 'Request debería ser local');
    }
}
