<?php

class WazzupClient {
	private $address;
	private $port;

	public function __construct($address, $port) {
		$this->address = $address;
		$this->port = $port;
	}

	public function connect() {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$in = "Wazzup!\n";

		socket_connect($socket, $this->address, $this->port);

		socket_write($socket, $in, strlen($in));

		while ( $msg = socket_read($socket, 2048, PHP_NORMAL_READ) ) {
		    echo $msg . PHP_EOL;
		}

		socket_close($socket);
	}
}
	$client = new WazzupClient('127.0.0.1', 55555);

	$client->connect();
?>