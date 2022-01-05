<?php

class Sockeador
{

  public $socket;
  public static $ip_address = '127.0.0.1';
  public static $port = 8888;

  public function __construct($sock)
  {
    $this->socket = $sock;
  }

  public static function crear_socket()
  {
    $sock = socket_create(AF_INET, SOCK_STREAM, 0);
    if (!$sock) {
      // if (!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);

      die("Couldn't create socket: [$errorcode] $errormsg \n");
    }
    //echo "Socket created\n";

    //Connect socket to remote server
    if (!socket_connect($sock, Sockeador::$ip_address, Sockeador::$port)) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);

      die("Could not connect: [$errorcode] $errormsg \n");
    }

    $socket = new Sockeador($sock);

    return $socket;
  }

  // Send to server
  public function enviar_al_socket($message)
  {

    //var_dump($this->socket);
    if (!socket_send($this->socket, $message, strlen($message), 0)) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);

      die("Could not send data: [$errorcode] $errormsg \n");
    }

    echo "Message send successfully \n";
  }

  //Receive reply from server
  public function recibir_del_socket()
  {
    //var_dump($this->socket);
    //if (!(socket_recv($this->socket, $buf, 2045, MSG_WAITALL))) {
    if (socket_recv($this->socket, $buf, 2045, MSG_WAITALL) === FALSE) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);

      die("Could not receive data: [$errorcode] $errormsg \n");
    }

    //print the received message
    echo 'Buff: ' . $buf;
    return $buf;
  }
}
