<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
  use _generated\ApiTesterActions;

  public function getDatosTest()
  {
    return $this->getSuiteConfig()['params'];
  }

  public function getSuiteConfig()
  {
    $config = \Codeception\Configuration::config();
    return \Codeception\Configuration::suiteSettings('api', $config);
  }
}
