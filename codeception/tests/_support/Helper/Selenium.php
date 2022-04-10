<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Selenium extends \Codeception\Module
{
  public function getWebDriver()
  {
      return $this->getModule('WebDriver')->webDriver;
  }
}
