<?php
require_once("../Network/Network.php");

class Route {
    private $gateway;
    private $interfaceName;
    private $metric;
    private $network;

    public function __construct(Network $network, IPv4Address $gateway, $interfaceName, $metric) {
        $this->network = $network;
        $this->gateway = $gateway;
        $this->interfaceName = $interfaceName;
        $this->metric = $metric;
    }

    public function getGateway() {
        return $this->gateway;
    }

    public function getInterfaceName() {
        return $this->interfaceName;
    }

    public function getMetric() {
        return $this->metric;
    }

    public function getNetwork() {
        return $this->network;
    }
    
    public function toString() {
        if ( $this->gateway->toLong() ) {
           return "net: " . $this->network->toString() 
                . ", gateway: " 
                . $this->gateway->toString()
                . ", interface: $this->interfaceName, metric: $this->metric";
        }
        return "net: " . $this->network->toString() 
             . ", interface: $this->interfaceName, metric: $this->metric";
    }
}
?>