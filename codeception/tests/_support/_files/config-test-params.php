<?php
$dir = dirname(__FILE__);
return [
  'BASE_FILE' => dirname($dir, 4) . '/',
  'MEM_FILE' => $dir . '/memoria_test.txt',
  'MEM_TEMP_FILE' => $dir .'/memoria_test_temp.txt',
  'AMIGAS_FILE' => $dir . '/memoria_test_amigas.txt',
  'host' => 'localhost',
  'port' => 8889,
  'dbname' => 'agenda',
  'dbuser' => 'root',
  'dbpassword' => 'root'
];
?>