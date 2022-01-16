<?php

require_once 'class.socketuser.php';
require_once 'cuatro.php';
class Sockeador
{
  // public static $host = '127.0.0.1';
  public static $host = 'localhost';
  public static $port = 9000;
  public $socket;
  public $clients = [];
  public $users = [];
  public $plays = [];

  public function __construct($sock)
  {
    $this->socket = $sock;
  }

  public function accept_new_user(&$changed)
  {
    $socket_new = socket_accept($this->socket); //accpet new socket
    $this->clients[] = $socket_new; //add socket to client array

    $header = socket_read($socket_new, 1024); //read data sent by the socket
    $this->perform_handshaking($header, $socket_new, self::$host, self::$port); //perform websocket handshake

    $this->users[max(array_keys($this->clients))] = new SocketUser();

    //socket_getpeername($socket_new, $ip); //get ip address of connected socket
    //$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' connected'))); //prepare json data
    //$this->send_message($response); //notify all users about new connection

    //make room for new socket
    $found_socket = array_search($this->socket, $changed);
    unset($changed[$found_socket]);
  }

  public function close_user($changed_socket)
  {
    $found_socket = array_search($changed_socket, $this->clients);
    //socket_getpeername($changed_socket, $ip);
    unset($this->clients[$found_socket]);
    $user = $this->users[$found_socket];
    unset($this->users[$found_socket]);

    //notify all users about disconnected connection
    $response = $this->mask(json_encode(array(
      'type' => 'system',
      'message' => $user->name . ' disconnected',
      'user' => $user->toArray()
    )));
    return $this->send_message($response);
  }

  public static function create_socket(): Sockeador
  {
    //Create TCP/IP sream socket
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    //reuseable port
    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

    //bind socket to specified host
    socket_bind($socket, 0, Sockeador::$port);

    //listen to port
    socket_listen($socket);

    return new Sockeador($socket);
  }

  public function manage_play(int $id_user, array $datos)
  {
    switch ($datos['message']) {
      case 'new play':
        $this->new_play_send($id_user, $datos);
        break;
      case 'accept play':
        $this->new_play_accept($id_user, $datos);
        break;
      case 'decline play':
        $this->new_play_decline($id_user, $datos);
        break;
      case 'move':
        $this->play_move($id_user, $datos);
        break;
      default:
    }

    return true;
  }

  public function manage_msg(int $id_user, array $datos)
  {
    $message = $datos['message'];
    $name = $this->users[$id_user]->name;
    $color = $this->users[$id_user]->color;

    $response_text = $this->mask(json_encode(array(
      'type' => 'usermsg', 
      'name' => $name, 
      'message' => $message, 
      'color' => $color
    )));
    return $this->send_message($response_text);
  }

  public function manage_system(int $id_user, array $datos)
  {
    if ($datos['message'] == 'set user data') {

      $this->users[$id_user]->id = $id_user;
      $this->users[$id_user]->name = $datos['name'] != '' ? $datos['name'] : 'Anonymous' . $id_user;
      $this->users[$id_user]->color = $datos['color'];

      $msg = $this->mask(json_encode(array(
        'type' => 'system',
        'message' => 'socket connected',
        'user' => $this->users[$id_user],
        'users' => $this->users
      )));
      $this->send_message($msg, $id_user);

      $msg = $this->mask(json_encode(array(
        'type' => 'system',
        'message' => $this->users[$id_user]->name . ' connected',
        'user' => $this->users[$id_user]
      )));
      return $this->send_message($msg);
    }
    return true;
  }

  public function new_play_accept(int $id_user, array $datos)
  {
    $id_op = intVal($datos['id_op']);
    $play = null;
    foreach ($this->plays as $p) {
      if (!$p->on && $p->player1 == $id_op && $p->player2 == $id_user) {
        $play = $p;
        break;
      }
    }

    if ($play) {
      $play->on = true;

      $response_text = $this->mask(json_encode(array(
        'type' => 'play',
        'message' => 'play accept',
        'id_op' => $id_user,
      )));
      $this->send_message($response_text, $id_op);

      $response_text = $this->mask(json_encode(array(
        'type' => 'usermsg',
        'message' => 'I accept your challenge!!',
        'name' => $this->users[$id_user]->name,
        'color' => $this->users[$id_user]->color,
      )));
      $this->send_message($response_text, $id_op);

      $play->cuatro = new Cuatro();
      $turno = $id_user;
      $contra = $id_op;
      if ($play->cuatro->iniciarTurno()) {
        $turno = $id_op;
        $contra = $id_user;
      }

      $arr_msg = array(
        'type' => 'play',
        'message' => 'move',
        'tablero' => array_merge(...$play->cuatro->tablero),
        'mensaje' => [
          "Partida compartida",
          "Tu, " . $this->users[$turno]->name . " tienes las fichas verdes.",
          "Yo, " . $this->users[$contra]->name . " llevo las fichas amarillas",
          "Elige una columna para comenzar"
        ],
        'turno' => true
      );

      $msg = $this->mask(json_encode($arr_msg));
      $this->send_message($msg, $turno);

      $arr_msg['mensaje'] = [
        "Partida compartida",
        "Tu, " . $this->users[$contra]->name . " tienes las fichas verdes.",
        "Yo, " . $this->users[$turno]->name . " llevo las fichas amarillas",
        "Ahora me toca a mí. Espera un momento..."
      ];
      $arr_msg['turno'] = false;
      $msg = $this->mask(json_encode($arr_msg));
      $this->send_message($msg, $contra);

      return true;
    }
    return false;
  }

  public function new_play_decline(int $id_user, array $datos)
  {
    $id_op = intVal($datos['id_op']);
    foreach ($this->plays as $i => $p) {
      if (!$p->on && $p->player1 == $id_op && $p->player2 == $id_user) {
        unset($this->plays[$i]);
        break;
      }
    }

    $response_text = $this->mask(json_encode(array(
      'type' => 'play',
      'message' => 'play decline',
      'id_op' => $id_user,
    )));
    $this->send_message($response_text, $id_op);

    $response_text = $this->mask(json_encode(array(
      'type' => 'usermsg',
      'message' => 'I decline your challenge. Maybe later.',
      'name' => $this->users[$id_user]->name,
      'color' => $this->users[$id_user]->color,
    )));
    $this->send_message($response_text, $id_op);

    return true;
  }

  public function new_play_send(int $id_user, array $datos)
  {
    $id_op = intVal($datos['id_op']);
    $this->plays[] = new SocketPlay($id_user, $id_op);

    $response_text = $this->mask(json_encode(array(
      'type' => 'play',
      'message' => 'play request',
      'id_op' => $id_user,
    )));
    $this->send_message($response_text, $id_op);

    $response_text = $this->mask(json_encode(array(
      'type' => 'usermsg',
      'name' => $this->users[$id_user]->name,
      'color' => $this->users[$id_user]->color,
      'message' => 'I sent you a play request!!',
    )));
    return $this->send_message($response_text, $id_op);
  }

  public function play_move(int $id_user, array $datos)
  {
    $play = null;
    $ind_play = -1;
    foreach ($this->plays as $i => $p) {
      if ($p->on && ($p->player1 == $id_user || $p->player2 == $id_user)) {
        $play = $p;
        $ind_play = $i;
        break;
      }
    }

    if ($play) {
      $res = $play->cuatro->echarFichaSocket($datos['tablero'], $datos['columna'], $this->users[$id_user]->name);

      $arr_res = array(
        'type' => 'play',
        'message' => 'move',
        'tablero' => $res['tablero'],
        'mensaje' => $res['mensaje'],
        'fin_partida' => $res['fin_partida'],
        'linea' => $res['linea'],
        'turno' => true
      );

      if ($res['token'] !== false) {

        $tablero_inv = [];
        foreach ($res['tablero'] as $token) {
          if ($token == 'H') $tablero_inv[] = 'M';
          else if ($token == 'M') $tablero_inv[] = 'H';
          else $tablero_inv[] = null;
        }
        $play->movs[] = $play->contador == 0 || $play->contador % 2 == 0 ? $res['tablero'] : $tablero_inv;
        $play->contador++;

        $contra = $play->player1 == $id_user ? $play->player2 : $play->player1;
        $arr_res['tablero'] = $tablero_inv;
        $msg = $this->mask(json_encode($arr_res));
        $this->send_message($msg, $contra);
        
        $arr_res['turno'] = false;
        $arr_res['tablero'] = $res['tablero'];
        $msg = $this->mask(json_encode($arr_res));
        $this->send_message($msg, $id_user);

        if ($res['fin_partida']) {
          $arr_res = array(
            'type' => 'play',
            'message' => 'end',
            'id_op'=> $contra
          );
          $msg = $this->mask(json_encode($arr_res));
          $this->send_message($msg, $id_user);

          $arr_res['id_op'] = $id_user;
          $msg = $this->mask(json_encode($arr_res));
          $this->send_message($msg, $contra);

          Cuatro::guardarPartida($play->movs, $play->contador);

          unset($this->plays[$ind_play]);
        }
      } else {

        $msg = $this->mask(json_encode($arr_res));
        $this->send_message($msg, $id_user);
      }

      return true;
    }
    return false;
  }

  function send_message($msg, int $to = 0)
  {
    if ($to > 0) {
      @socket_write($this->clients[$to], $msg, strlen($msg));
    } else {
      foreach ($this->clients as $changed_socket) {
        @socket_write($changed_socket, $msg, strlen($msg));
      }
    }
    return true;
  }

  //Unmask incoming framed message
  function unmask($text)
  {
    $length = ord($text[1]) & 127;
    if ($length == 126) {
      $masks = substr($text, 4, 4);
      $data = substr($text, 8);
    } elseif ($length == 127) {
      $masks = substr($text, 10, 4);
      $data = substr($text, 14);
    } else {
      $masks = substr($text, 2, 4);
      $data = substr($text, 6);
    }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
      $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
  }

  //Encode message for transfer to client.
  function mask($text)
  {
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125)
      $header = pack('CC', $b1, $length);
    elseif ($length > 125 && $length < 65536)
      $header = pack('CCn', $b1, 126, $length);
    elseif ($length >= 65536)
      $header = pack('CCNN', $b1, 127, $length);
    return $header . $text;
  }

  //handshake new client.
  function perform_handshaking($receved_header, $client_conn, $host, $port)
  {
    $headers = array();
    $lines = preg_split("/\r\n/", $receved_header);
    foreach ($lines as $line) {
      $line = chop($line);
      if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
        $headers[$matches[1]] = $matches[2];
      }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    //hand shaking header
    $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
      "Upgrade: websocket\r\n" .
      "Connection: Upgrade\r\n" .
      "WebSocket-Origin: $host\r\n" .
      "WebSocket-Location: ws://$host:$port/demo/shout.php\r\n" .
      "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn, $upgrade, strlen($upgrade));
  }
}
