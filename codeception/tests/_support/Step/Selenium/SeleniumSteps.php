<?php

namespace Step\Selenium;

use Codeception\Scenario;
use SeleniumTester;

class SeleniumSteps //extends \SeleniumTester
{

  public $pagina;
  protected $I;
  protected $escenario;
  // Steps de InitFeature
  public function __construct(SeleniumTester $I, Scenario $scenario)
  {
    // var_dump($uno);
    // var_dump($sce->g);
    $this->I = $I;
    $this->escenario = $scenario;
  }
  /**
   * @Given que voy a :index
   */
  // * @Given /que voy a (\w+)/
  public function queVoyAIndex($pagina)
  {
    if ($pagina == 'index') $pagina = '';
    //throw new \PHPUnit\Framework\IncompleteTestError("Step `que voy al index` is not defined");
    $this->pagina = '/' . $pagina;
  }
  /**
   * @Given que voy a ping
   */
  public function queVoyAPing()
  {
    $this->I->amOnPage('/ping.php');

    $this->res = $this->I->grabPageSource();

    // throw new \PHPUnit\Framework\IncompleteTestError("Step `que voy a ping` is not defined");
  }

  /**
   * @Then recibo datos de ping
   */
  public function reciboDatosDePing()
  {
    $res = str_replace('<html><head></head><body>', '', $this->res);
    $res = str_replace('</body></html>', '', $res);
    $res = json_decode($res, true);
    // var_dump($res);
    $this->I->assertEquals($res['clientIp'], '127.0.0.1', 'ClientIp debería ser local');
    // throw new \PHPUnit\Framework\IncompleteTestError("Step `recibo datos de ping` is not defined");
  }

  /**
   * @When voy a la pagina
   */
  public function voyALaPagina()
  {
    $this->I->amOnPage($this->pagina);
    // $this->amOnPage($this->pagina);
  }

  /**
   * @When veo el titulo
   */
  public function veoElTitulo()
  {
    $this->I->see('Cuatro en raya', 'h1');
    // $this->see('Cuatro en raya', 'h1');
  }

  /**
   * @When /pulso boton con id ([\w\-_]+)/
   */
  public function pulsoBotonConId(string $id)
  {
    $this->I->click("#" . $id);
    // $this->click("#" . $id);
  }

  /**
   * @Then veo partida automática
   */
  public function veoPartidaAutomtica()
  {
    // $headers = $this->get_headers();
    // var_dump(get_object_vars($this->getenv()));
    $this->I->debugWebDriverLogs();
    // var_dump($this->escenario->getFeature());
    // var_dump($this->escenario->current('browser'));
    // $helper = New Helper();
    // var_dump($this->I->getWebDriver());
    $this->I->makeScreenshot('jarjar/jajr');
    // $pageSource = $this->grabPageSource();
    // // Coincidencia total de className
    // // $arr_tokens_M = $this->grabMultiple('.token.M', 'class');
    // // $arr_tokens_M = $this->grabMultiple('//div[@class="token M"]', 'class');
    // // Coincidencia parcial de className
    // $arr_tokens_M = $this->grabMultiple('//div[contains(@class,"token M")]', 'class');
    // $this->assertGreaterThan(0, count($arr_tokens_M), 'Debería tener más de 0 elementos');
    // // $this->assertEquals(6, count($arr_tokens_M), 'Debería tener 6 elementos si no gana');
    // $this->seeNumberOfElements(".token.M", count($arr_tokens_M));
    // var_dump($a);
    // var_dump($this);
  }
}
