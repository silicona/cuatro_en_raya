<?php

require_once dirname(__FILE__) . '/_files/config-test.php';

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
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;
    public $params;
    /**
     * Define custom actions here
     */
    public function __construct(\Codeception\Scenario $scenario)
    {
        $this->scenario = $scenario;
    
        $this->params = \Codeception\Configuration::suiteSettings("unit", \Codeception\Configuration::config())['params'];
    }
}
