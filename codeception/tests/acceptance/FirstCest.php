<?php
class FirstCest
{
  public function _failed(AcceptanceTester $I, $fail)
  {
    // $this->debug($test);
    var_dump(get_object_vars($I));
    print_r($this);
    //var_dump($I->);
  }

  public function frontpageWorks(AcceptanceTester $I)
  {
    $I->amOnPage('/');
    $I->seeResponseCodeIs(200);
    $I->see('Cuatro en raya', 'h1');
    $I->seeElement('h2');

    // $I->debug('Request');
  }
}
