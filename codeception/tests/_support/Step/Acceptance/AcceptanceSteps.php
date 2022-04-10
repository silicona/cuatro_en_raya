<?php
// namespace Step\Acceptance;

// class AcceptanceSteps extends \AcceptanceTester
// {

//   public $pagina;
//     // Steps de InitFeature
//     /**
//      * @Given que voy a :index
//      */
//     // * @Given /que voy a (\w+)/
//     public function queVoyAIndex($pagina)
//     {
//         if($pagina == 'index') $pagina = '';
//         //throw new \PHPUnit\Framework\IncompleteTestError("Step `que voy al index` is not defined");
//         $this->pagina = '/'.$pagina;
//     }

//     /**
//      * @When voy a la pagina
//      */
//     public function voyALaPagina()
//     {
//         //throw new \PHPUnit\Framework\IncompleteTestError("Step `voy al pagina` is not defined");
//         $this->amOnPage($this->pagina);
//     }

//     /**
//      * @When veo el titulo
//      */
//     public function veoElTitulo()
//     {
//         //throw new \PHPUnit\Framework\IncompleteTestError("Step `veo el titulo` is not defined");
//         $this->see('Cuatro en raya', 'h1');
//     }

//     /**
//      * @When /pulso boton con id ([\w\-_]+)/
//      */
//     public function pulsoBotonConId(string $id)
//     {
//         $this->click("#" . $id);
//     }
        
//     /**
//      * @Then veo partida automática
//      */
//     public function veoPartidaAutomtica()
//     {
//         $a = $this->grabPageSource();
//         $a = $this->grabMultiple('//div[@class="tok"]', 'class');
//         // var_dump($a);
//         // $this->assertTrue(count($a) > 0);
//         $this->seeNumberOfElements(".token.M", count($a));
//         // var_dump($this);
//     }
// }