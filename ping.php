<?php

require_once 'vendor/autoload.php';

use CuatroPhp\php\SocketUser;
use CuatroPhp\php\Cuatro;

function getUserIpAddress()
{
  $serverVars = [
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
  ];
  foreach ($serverVars as $key) {
    if (array_key_exists($key, $_SERVER)) {

      foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
          return false;
        }

        // Localhost ip
        // if (!filter_var($ip, FILTER_FLAG_NO_PRIV_RANGE)) {
        //   return false;
        // }

        return $ip;
      }
    }
  }
};


$user = new Cuatro();
// $user = new SocketUser();

echo json_encode(array(
  'ping' => true,
  'user' => $user
  //'clientIp' => getUserIpAddress(),
  //'request' => $_REQUEST,
  //'server' => $_SERVER,
));
