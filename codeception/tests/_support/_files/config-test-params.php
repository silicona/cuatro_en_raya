<?php
$dir = dirname(__FILE__);

define('BASE_FILE', dirname($dir, 5) . '/');
define('MEM_FILE', $dir . '/memoria_test.txt');
//define('MEM_FILE', BASE_FILE . 'cuatro_php/php/memoria_bender.txt');
define('BASE_TEMP', $dir . '/');
define('AMIGAS_FILE', $dir . '/memoria_test_amigas.txt');

return [

  'MEM_FILE' => MEM_FILE,
  'BASE_URL' => 'http://localhost:8888/cuatro_php/',

  'host' => 'localhost',
  'port' => 8889,
  'dbname' => 'agenda',
  'dbuser' => 'root',
  'dbpassword' => 'root'
];

?>