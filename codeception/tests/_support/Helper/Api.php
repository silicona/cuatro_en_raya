<?php

namespace Helper;

use \Codeception\TestInterface;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{
  protected $test;

  function _before(TestInterface $test)
  {
    $this->test = $test;
  }

  function assertJar($expected, $actual, $message = '')
  {
    $this->assertSame($expected, $actual, $message);
  }
}
