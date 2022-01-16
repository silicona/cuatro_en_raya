<?php
class FirstCest
{
  public function _failed(AcceptanceTester $I, \Codeception\Scenario $scenario)
  {
    var_dump('Failed: ' . $scenario->current('name'));
    //var_dump(get_object_vars($I));
  }

  /**
   * @group wip
   */
  public function frontpageWorks(AcceptanceTester $I, \Codeception\Scenario $scenario)
  {
    $I->amOnPage('/');
    $I->seeResponseCodeIs(200);
    $I->see('Cuatro en raya', 'h1');
    $I->seeElement('h2');
    // $I->debug('Request');
  }

  public function testSkippedAProposito(AcceptanceTester $I, \Codeception\Scenario $scenario)
  {
    $scenario->skip('Test skipped by ominous hand, Jorjorjor');
  }
}
