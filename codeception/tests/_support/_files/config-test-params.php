<?php
$dir = dirname(__FILE__);

define('BASE_FILE', dirname($dir, 4) . '/');
define('BASE_TEMP', $dir . '/');
define('MEM_FILE', $dir . '/memoria_test.txt');
//define('MEM_FILE', BASE_FILE . 'cuatro_php/php/memoria_bender.txt');
define('AMIGAS_FILE', $dir . '/memoria_test_amigas.txt');

// Pasan por params en yml
return [

  //'BASE_APP' => dirname($dir, 4) . '/',
  'MEM_FILE' => MEM_FILE,
  'BASE_FILE' => BASE_FILE,
  'BASE_TEST_FILE' => BASE_TEMP,
  'AMIGAS_FILE' => AMIGAS_FILE,

  'BASE_URL' => 'http://localhost:8888/cuatro_php/',

  'host' => 'localhost',
  'port' => 8889,
  'dbname' => 'agenda',
  'dbuser' => 'root',
  'dbpassword' => 'root'
];

?>