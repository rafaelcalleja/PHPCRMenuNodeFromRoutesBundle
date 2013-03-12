<?php
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;
use Doctrine\ODM\PHPCR\Event\MoveEventArgs;

use RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteDataEvent;
use RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteMoveEventsData;
use RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteEvents;

class EventDispatcher
{
	private $dm;
	private $dispatcher;
	
	function __construct(DocumentManager $dm, $dispatcher){
		$this->dm =$dm;
		$this->dispatcher = $dispatcher;
	}
	

	public function postPersist(LifecycleEventArgs $event){
		$document = $event->getDocument(); 
		
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			$newevent = new RouteDataEvent($event);
			$this->dispatcher->dispatch(RouteEvents::ROUTE_ADDED, $newevent);
		}
	}
	
	public function postMove(MoveEventArgs $event){
		
		$document = $event->getDocument();
		
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			$newevent = new RouteMoveEventsData($event);
			$this->dispatcher->dispatch(RouteEvents::ROUTE_POST_MOVE, $newevent);
		}
	}	
	
	public function preUpdate(LifecycleEventArgs $event){
		$document = $event->getDocument();
		
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			$newevent = new RouteDataEvent($event);
			$this->dispatcher->dispatch(RouteEvents::ROUTE_PRE_EDITED, $newevent);
		}
	}
	
	public function postUpdate(LifecycleEventArgs $event){
		$document = $event->getDocument();
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			$newevent = new RouteDataEvent($event);
			$this->dispatcher->dispatch(RouteEvents::ROUTE_EDITED, $newevent);
		}
	
	}	
	
}