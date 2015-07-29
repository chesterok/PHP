<?php
require_once("../Route/Route.php");

class Router {
    private $routes = array();

    public function __construct(Array $routes) {
        $this->routes = $routes;
        usort($this->routes, array("Router", "cmp"));
    }

    private function cmp($a, $b) {
        $x = $a->getNetwork()->getMaskLength();
        $y = $b->getNetwork()->getMaskLength();

        if ( $x == $y ) {
            if ( $a->getMetric() == $b->getMetric() ) {
                return 0;
            }
            return ( $a->getMetric() > $b->getMetric() ) ? +1 : -1;
        }
        return ( $x < $y ) ? +1 : -1;
    }

    public function addRoute(Route $route) {
        array_push($this->routes, $route);
        usort($this->routes, array("Router", "cmp"));
    }

    public function getRouteForAddress(IPv4Address $address) {
        foreach ( $this->routes as $route ) {
            if ( $route->getNetwork()->contains($address) ) {
                return $route;
            }
        }
        return null;
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function removeRoute(Route $route) {
        unset($this->routes[array_search($route, $this->routes)]);
    }
}
?>