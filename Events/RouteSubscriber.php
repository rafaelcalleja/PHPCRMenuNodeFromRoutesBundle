<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RouteSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
				'kernel.response' => array(
						array('onKernelResponsePre', 10),
						array('onKernelResponseMid', 5),
						array('onKernelResponsePost', 0),
				),
				'rc.route.added'     => array('onRouteAdded', 0),
				'rc.route.edited'     => array('onRouteEdited', 99),
		);
	}

	public function onKernelResponsePre(FilterResponseEvent $event){
	}

	public function onKernelResponseMid(FilterResponseEvent $event){
		
	}

	public function onKernelResponsePost(FilterResponseEvent $event){

	}

	public function onRouteAdded(RouteDataEvent $event){
	}
	
	public function onRouteEdited(RouteDataEvent $event){
	}
}