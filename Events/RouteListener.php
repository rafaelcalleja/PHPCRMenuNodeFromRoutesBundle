<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteDataEvent;
use RC\PHPCRMenuNodeFromRoutesBundle\Events\RouteMoveEventsData;

class RouteListener {
	protected $routebase, $menubase, $menuname, $ms;
	protected $preedited = false;
	protected $pre_event;
	
	public function __construct($ms, $routebase, $menubase, $menuname){
		$this->ms = $ms;
		$this->menubase = $menubase;
		$this->routebase = $routebase;	
		$this->menuname = $menuname;
	}
	
	protected function getMenuName(RouteDataEvent $event){
		return $this->menubase.'/'.$this->menuname.'_'.$event->getLocale();		
	}
	
	protected function getId(RouteDataEvent $event){
		return str_replace( $this->routebase, $this->getMenuName($event), $event->getId());
	}
	
	protected function getName(RouteDataEvent $event){
		if( $event->getId() === $this->routebase ) return basename($this->getMenuName($event));
		return basename($event->getId());
	}
	
	protected function getParentId(RouteDataEvent $event){
		return dirname($this->getId($event));	
	}
	
	protected function createMenu($basename, $name, $label, $uri){
		
	}
	
	protected function newSource($event){
		return str_replace( $this->routebase, $this->getMenuName($event), $event->getSource());
	}
	
	protected function newDest($event){
		return str_replace( $this->routebase, $this->getMenuName($event), $event->getDest());
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
// 		$label = array($event->getLocale() => $event->getLabel());
// 		$uri = array($event->getLocale() => $event->getPath());
		
	}
	
	
	
	public function onRoutePreMoved(RouteMoveEventsData $event){
// 		var_dump('premove');
// 		$this->ms->updateMenu($event->getId(), $event->getDocument());
	}
	
	public function onRouteMoved(RouteMoveEventsData $event){
 		$this->ms->move_menu($this->newSource($event), $this->newDest($event));
 		$this->ms->updateMenu($this->newDest($event), $event->getDest(), $this->getName($event));
	}
	
	public function onRoutePreEdited(RouteDataEvent $event){
		//var_dump($event->getId());
// 		if(!$this->pre_event) $this->pre_event = $event;
// 		$this->preedited = true;
// 		var_dump('eddiasd', $event->getId(), $this->pre_event->getId(), $this->getId($event), $this->getId($this->pre_event));
// 		$this->pre_event = $event;
		//$this->ms->updateMenu($event->getId(), $event->getDocument());
	}
	
	public function onRouteEdited(RouteDataEvent $event){
		//var_dump('eddiasd', $event->getId(), $this->pre_event->getId(), $this->getId($event), $this->getId($this->pre_event));
		//$this->ms->updateMenu($event->getId(), $event->getDocument());
		//die(var_dump($event->getPath(), $this->getId($event), $this->pre_event->getPath()));
		
	}
	
}