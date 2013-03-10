<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteDataEvent;

class RouteListener {
	
	public function onRouteAdded(RouteDataEvent $event){
		var_dump($event->getLabel());
	}
	
}