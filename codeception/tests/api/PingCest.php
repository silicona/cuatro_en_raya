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

        $I->assertTrue($res['ping'], 'ping debería ser true');
    }
}
