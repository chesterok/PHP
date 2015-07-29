<?php

class EchoServer {
   private $addres;
   private $port;
   private $isRun;

   public function __construct($port=55555) {
      $this->port = $port;
      $this->isRun = false;
      $this->address = 'localhost';
   }

   public function isRunning() {
      return $this->isRun;
   }

   public function getPort() {
      return $this->port;
   }

   public function start() {
      $this->isRun = true;
      $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      $clients = array();

      socket_bind($sock, $this->address, $this->port);
      socket_listen($sock);

      while ( true ) {
         $read = array_merge(array($sock),$clients);

         socket_select($read, $write = NULL, $except = NULL, 50);

         if ( in_array($sock, $read) ) {
            $msgsock = socket_accept($sock);
            $clients[] = $msgsock;
         }
          
         foreach($clients as $key => $client) {
            if ( in_array($client, $read) ) {
               if ( !($msg = socket_read($client, 2048)) ) {
                  unset($clients[$key]);
                  socket_close($client);
                  break;
               }

               if ( trim($msg) == "disconnect" ) {
                  unset($clients[$key]);
                  socket_close($client);
                  break;
               }
               socket_write($client, $msg, strlen($msg));
            }
         }
      }
   }

   public function stop() {
      socket_close($sock);
      $this->isRun = false;
   }
}
?>