<?php

class InitialBbddCest
{
    public function _before(BbddTester $I)
    {
    }
    public function _failed(BbddTester $I)
    {
        var_dump('Faileddddddd');
        //$this->debug('jar');
    }

    public function tryToTest(BbddTester $I)
    {
        $I->seeInDatabase('contactos', ['nombre' => 'koko']);

        $idContacto = $I->grabFromDatabase('contactos', 'idContacto', ['nombre' => 'koko']);
        $I->assertGreaterThan(0, $idContacto, 'IdContacto debería ser mayor que 0');
    }

    public function trySelectAll(BbddTester $I)
    {
        $data = $I->selectAll('contactos');
        $I->assertGreaterThan(0, count($data), 'Debería tener más de 0 resultados');
        $I->assertSame($data[0]['idContacto'], '1');
    }

    public function trySelectAllByColumn(BbddTester $I)
    {
        $data = $I->selectAll('contactos', [], 'nombre');
        $I->assertGreaterThan(0, count($data), 'Debería tener más de 0 resultados');
    }

    public function testDoctrine(BbddTester $I)
    {
        // $em = $I->getModule('Doctrine2')->em;
        // codecept_debug($em);
        //$data = $I->selectAll('contactos', [], 'nombre');
        //$I->assertGreaterThan(0, count($data), 'Debería tener más de 0 resultados');
    }
}
