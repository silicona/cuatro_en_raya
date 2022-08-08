<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{
  public function seeMyVar($var)
  {
    $this->debug($var);
  }

  public function callMethod(object $obj, string $functionName, array $args) {
    $class = new \ReflectionClass($obj);
    $method = $class->getMethod($functionName);
    $method->setAccessible(true);
    return $method->invokeArgs($obj, $args);
  }
}
