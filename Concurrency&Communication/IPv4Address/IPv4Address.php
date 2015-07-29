<?php

class IPv4Address {
    private $ip;
    private $long;

    public function __construct($address) {
        if ( is_string($address) ) {
            $this->long = $this->ip2long($address);
            $this->ip = $address;
        } else {
            $this->ip = $this->long2ip($address);
            $this->long = $address;
        }
    }

    private function ip2long($ip) {
        $long = 0;
        $ip = explode(".", $ip);

        for ( $i = 0; $i < 3; $i++ ) {
            $long += $ip[$i];
            $long *= 256;
        }

        return (int)$long + $ip[3];
    }

    private function long2ip($long) {
        $ip = "";

        if ( $long < 0 ) {
            $long = sprintf("%u", $long);
        }

        for ( $i = 3; $i >= 0; $i-- ) {
            $ip .= (int)($long / pow(256, $i));
            $long -= (int)($long / pow(256, $i)) * pow(256, $i);

            if ( $i > 0 ) {
                $ip .= ".";
            } 
        }
        return $ip;
    }
   
    public function lessThan(IPv4Address $address) {
        return $this->long >= $address->toLong();
    }

    public function greaterThan(IPv4Address $address) {
        return $this->long <= $address->toLong();
    }

    public function equals(IPv4Address $address) {
        return $this->long === $address->toLong();
    }

    public function toString() {
        return $this->ip;
    }

    public function toLong() {
        return $this->long;
    }
}
?>