<?php
$dir = dirname(__FILE__);


define('BASE_FILE', dirname($dir, 5) . '/');
//define('MEM_FILE', $dir . '/memoria_test.txt');
define('MEM_FILE', BASE_FILE . 'cuatro_php/php/memoria_bender.txt');
define('BASE_TEMP', $dir . '/');
define('AMIGAS_FILE', $dir . '/memoria_test_amigas.txt');

return [

  // 'BASE_FILE' => dirname($dir, 4) . '/',
  'MEM_FILE' => MEM_FILE,
  // 'MEM_FILE' => $dir . '/memoria_test.txt',
  //'BASE_TEMP' => $dir .'/',
  //'MEM_TEMP_FILE' => $dir .'/memoria_test_temp.txt',
  //'AMIGAS_FILE' => $dir . '/memoria_test_amigas.txt',
  'BASE_URL' => 'http://localhost:8888/',

  'host' => 'localhost',
  'port' => 8889,
  'dbname' => 'agenda',
  'dbuser' => 'root',
  'dbpassword' => 'root'
];

?>