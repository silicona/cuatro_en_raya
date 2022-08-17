<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use CuatroPhp\php\Sockeador;

/**
 * Thousand thanks to WebSocket Master http://www.sanwebe.com/2013/05/chat-using-websocket-php-socket
 * @License : http://opensource.org/licenses/MIT
 */
require_once 'config.php';
//require_once 'class.sockeador.php';

$socker = Sockeador::create_socket();
$socket = $socker->socket;
$socker->clients = array($socket);	//create & add listning socket to the list

//start endless loop, so that our script doesn't stop
while (true) {
	$changed = $socker->clients;	//manage multipal connections
	socket_select($changed, $null, $null, 0, 10);		//returns the socket resources in $changed array

	if (in_array($socket, $changed)) {	//check for new socket
		$socker->accept_new_user($changed);
	}

	foreach ($changed as $id_user => $changed_socket) {		//loop through all connected sockets

		while (@socket_recv($changed_socket, $buf, 1024, 0) >= 1)	//check for any incomming data
		{
			$received_text = $socker->unmask($buf); //unmask data
			$tst_msg = json_decode($received_text, true); //json decode

			if ($tst_msg) {
				switch ($tst_msg['type']) {
					case 'system':
						$socker->manage_system($id_user, $tst_msg);
						break;
					case 'play':
						$socker->manage_play($id_user, $tst_msg);
						break;
					case 'usermsg':
						$socker->manage_msg($id_user, $tst_msg);
						break;
				}
			}

			break 2; //exist this loop
		}

		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) {	// check disconnected client
			$socker->close_user($changed_socket);
		}
	}
}
socket_close($socket);	// close the listening socket
