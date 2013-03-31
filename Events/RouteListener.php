<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use RC\PHPCRRouteEventsBundle\Events\RouteDataEvent;
use RC\PHPCRRouteEventsBundle\Events\RouteMoveEventsData;
use RC\PHPCRRouteEventsBundle\Events\RouteFlushDataEvent;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;


class RouteListener {
	protected $routebase, $menubase, $menuname, $ms;
	protected $preedited = false;
	protected $pre_event;
	
	public function __construct($ms, $menubase, $menuname){
		$this->ms = $ms;
		$this->menubase = $menubase;
		$this->menuname = $menuname;
	}
	
	protected function getMenuName(RouteDataEvent $event){
		return $this->menubase.'/'.$this->menuname.'_'.$event->getLocale();		
	}
	
	protected function getId(RouteDataEvent $event){
		return str_replace( $event->getDocument()->getPrefix(), $this->getMenuName($event), $event->getId());
	}
	
	protected function getName(RouteDataEvent $event){
		if( $event->getId() === $event->getDocument()->getPrefix() ) return basename($this->getMenuName($event));
		return basename($event->getId());
	}
	
	protected function getParentId(RouteDataEvent $event){
		return dirname($this->getId($event));	
	}
	
	protected function createMenu($basename, $name, $label, $uri){
		
	}
	
	protected function newSource($event){
		return str_replace( $event->getDocument()->getPrefix(), $this->getMenuName($event), $event->getSource());
	}
	
	protected function newDest($event){
		return str_replace( $event->getDocument()->getPrefix(), $this->getMenuName($event), $event->getDest());
	}
	
	public function onRouteAdded(RouteDataEvent $event){
		$basename = $this->getParentId($event);
		$name = $this->getName($event);
		$label = $event->getLabel();
		$uri = $event->getPath();
		if(!$label){
			$this->ms->createMenuRoot($this->menubase, $this->menuname );
		}else{
			$this->ms->createMenuItem($basename, $name, $label, $uri);
		}
		
	}

	
	public function onRouteMoved(RouteMoveEventsData $event){
		$old = $event->getDocumentManager()->getPhpcrSession()->getNode(dirname($this->newDest($event)))->getNodeNames()->getArrayCopy();
 		$this->ms->move_menu($this->newSource($event), $this->newDest($event));
 		$newOrder = $event->getDocumentManager()->getPhpcrSession()->getNode(dirname($this->newDest($event)))->getNodeNames()->getArrayCopy();
 		$position = key(array_diff($old, $newOrder));
 		$this->ms->updateMenu($this->newDest($event), $event->getDest(), $this->getName($event), $event->getDocument()->getRouteContent()->getTitle());
 		$old = $newOrder;
 		$inserted = array_pop($newOrder);
 		array_splice($newOrder, $position, 0, $inserted );
 		$this->ms->reorderMenu($old, $newOrder, dirname($this->newDest($event)));
	}
	
	
	public function onRouteRemoved(RouteFlushDataEvent $event){
		$documents = $event->getRemoved();
		foreach($documents as $d){
			$newEvent = new RouteDataEvent(new LifecycleEventArgs($d, $event->getDocumentManager()));
			$basename = $this->getParentId($newEvent);
			$name = $this->getName($newEvent);
			$this->ms->remove("$basename/$name");
			
		}
	}
	
	public function onRoutePreEdited(RouteDataEvent $event){
		
	}
	
	public function onRouteEdited(RouteDataEvent $event){
				
	}
	

}