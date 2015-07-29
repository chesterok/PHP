<?php

class ServerSetting {
    private function getDataByFile($fileName) {
        $file = file($fileName);
        $data = array();

        foreach ( $file as $val ) {
            if ( $val != "\r\n" ) {
                $data[trim(strstr($val, "  ", true))] = trim(strstr($val, "  "));
            }
        }
        return $data;
    }

    public function getAddress() {
        return $this->getDataByFile('http.conf')['address'];
    }

    public function getPort() {
        return $this->getDataByFile('http.conf')['port'];
    }

    public function getMimiTypes($fileName) {
        $extension = substr(strrchr($fileName, '.'), 1);
        $mimiTypes = $this->getDataByFile('mime.types');

        if ( isset($mimiTypes[$extension]) ) {
            return $mimiTypes[$extension];
        } else {
            return "application/octet-stream";
        }
    }
}

class HttpServer {
    private $address;
    private $port;
    private $sock;
    private $setting;
    private $clients = array();
    private $requests = array();
    private $read = array();
    private $code;
    private $response;
    private $responseNotFound = "HTTP/1.1 404 Not Found\n";
    private $responseMethodNotAllowed = "HTTP/1.1 405 Method Not Allowed\n";
    private $responseOk = "HTTP/1.1 200 OK\n"
                      . "Connection: close\n"
                      . "Content-Length: %d\n"
                      . "Content-Type: %s\n"
                      . "Date: %s\n\n"
                      . "%s\n";

    public function __construct(ServerSetting $setting) {
        $this->address = $setting->getAddress();
        $this->port = $setting->getPort();
        $this->setting = $setting;
    }

    private function run() {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_bind($this->sock, $this->address, $this->port);
    }

    private function addListener() {
        $this->read = array_merge(array($this->sock), $this->clients);

        socket_select($this->read, $write = NULL, $except = NULL, 50);

        if ( in_array($this->sock, $this->read) ) {
           $this->clients[] = socket_accept($this->sock);
        }
    }

    private function readRequest($key, $client) {
        if ( !($msg = socket_read($client, 2048)) ) {
            unset($this->requests[$client]);
            socket_close($client);
            unset($this->clients[$key]);

            return false;
        }

        if ( isset($this->requests[$client]) ) {
            $this->requests[$client] .= $msg;
        } else {
            $this->requests[$client] = $msg;
        }

        return true;
    }

    private function consoleOutput($code, $path) {
        if ( isset($path) ) {
            echo $code . $path . PHP_EOL;
        } else {
            echo $code . PHP_EOL;
        }
    }

    private function reply($client) {
        if ( substr($this->requests[$client], -4) == "\r\n\r\n" ) {
            $request = explode(' ', $this->requests[$client]);
            $this->requests[$client] = '';
            
            if ( $request[0] == 'GET' ) {
                $path = $request[1];

                if ( $path == '/' ) {
                    $path .= 'index.html';
                }

                $file = basename($path);

                if ( file_exists($file) ) {
                    $this->response = sprintf($this->responseOk,
                        filesize($file),
                        $this->setting->getMimiTypes($path),
                        date("D, j M H:i:s T"),
                        file_get_contents($file)
                    );
                    $this->code = '200 ';
                } else {
                    $this->response = $this->responseNotFound;
                    $this->code = '404 ';
                }
            } else {
                $this->response = $this->responseMethodNotAllowed;
                $this->code = '405 ';
                $path = null;
            }
            socket_write($client, $this->response, strlen($this->response));
            $this->consoleOutput($this->code, $path);
        }
    }

    private function listen() {
        socket_listen($this->sock);

        while ( true ) {
            $this->addListener();
              
            foreach( $this->clients as $key => $client ) {

                if ( in_array($client, $this->read) ) {

                    if ( !$this->readRequest($key, $client) ) {
                        break;
                    }
                    $this->reply($client);
                }
            }
        }
    }

    public function start() {
        $this->run();
        $this->listen();
    }
}
    $server = new HttpServer(new ServerSetting());

    $server->start();
?>