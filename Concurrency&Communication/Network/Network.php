<?php
require_once("../Ipv4Address/Ipv4Address.php");

class Network {
	private $maskLength;
	private $address;
	private $net;
	private $maskString;
	private $mask;
	private $broadcastAddress;
	private $firstUsableAddress;
	private $lastUsableAddress;
	private $totalHosts;

	public function __construct(IPv4Address $address, $maskLength) {
		$this->maskLength = $maskLength;
		$this->maskString = long2ip(-1 << (32 - $maskLength));
		$this->mask = ip2long($this->maskString);;
		$this->address = new IPv4Address( ($this->getNet($address)) );
		$this->net = $this->address->toString() . '/' . $maskLength;
		$this->broadcastAddress = new IPv4Address($this->address->toLong() + ~$this->mask);
		$this->lastUsableAddress = new IPv4Address(($this->address->toLong() + ~$this->mask) - 1);
		$this->firstUsableAddress = new IPv4Address($this->getNet($address) + 1);
		$this->totalHosts = $this->maskLength == 32 ? 1 : (int)(pow(2, 32) - $this->mask - 2);
	}

    private function getNet($address) {
        return $this->mask & $address->toLong();
    }

	public function toString() {
		return $this->net;
	}

    public function contains(IPv4Address $address) {
        return $address->toLong() >= $this->firstUsableAddress->toLong() &&
            $address->toLong() <= $this->lastUsableAddress->toLong();
    }

    public function getAddress() {
    	return $this->address;
    }

    public function getBroadcastAddress() {
    	return $this->broadcastAddress;
    }

    public function getFirstUsableAddress() {
    	return $this->firstUsableAddress;
    }

    public function getLastUsableAddress() {
    	return $this->lastUsableAddress;
    }

    public function getMask() {
    	return $this->mask;
    }

    public function getMaskString() {
    	return $this->maskString;
    }

    public function getMaskLength() {
    	return $this->maskLength;
    }

    public function getSubnets() {
    	$first = new Network($this->address, $this->maskLength + 1);
    	$second = new Network(new Ipv4Address($first->getBroadcastAddress()->toLong() + 1), $this->maskLength + 1);

    	return array($first, $second);
    }

    public function getTotalHosts() {
    	return $this->totalHosts;
    }

    public function isPublic() {
    	if ( $this->address->lessThan(new IPv4Address('10.0.0.0')) &&
            $this->address->greaterThan(new IPv4Address('10.255.255.255')) ) {

    		return false;
    	}
    	if ( $this->address->lessThan(new IPv4Address('172.16.0.0')) &&
            $this->address->greaterThan(new IPv4Address('172.31.255.255')) ) {

    		return false;
    	}
    	if ( $this->address->lessThan(new IPv4Address('192.168.0.0')) &&
            $this->address->greaterThan(new IPv4Address('192.168.255.255')) ) {

    		return false;
    	}
    	return true;
    }
}
?>