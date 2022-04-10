<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://apiv2.avirato.com/ping');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);
$data = curl_exec($ch);
curl_close($ch);

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

$data = json_decode($data, true);

echo json_encode(array(
  'ping' => true,
  'clientIp' => getUserIpAddress(),
  'request' => $_REQUEST,
  'server' => $_SERVER,
  'api2' => $data,
  'api2ping' => array(
    'ping' => $data['ping'],
    'host' => $data['host'],
    'ipHost' => $data['x-forwarded-for']
  )
));
