<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;

class RouteDataEvent extends Event
{
	protected $document, $dm;

	public function __construct(LifecycleEventArgs $event){
		$this->document = $event->getDocument();
		$this->dm = $event->getDocumentManager();
	}

	/*
	 *
	id: conocenos1
	parent: lateral/conocenos
	parent_label:
	en: conocenos_en
	es: conocenos
	label:
	en: Nuestra misión EN
	es: Nuestra misión
	uri: true
	*/
	
	public function getLocale(){
		return $this->document->getDefault('_locale');
	}
	
	public function getPath(){
		return $this->document->getPattern();
	}
	
	public function getId(){
		var_dump(get_class($this->document->getRouteContent()));
		return $this->document->getId();
	}
	
	public function getLabel(){
		
		return (method_exists($this->document->getRouteContent(), 'getTitle')) ? $this->document->getRouteContent()->getTitle() : false;
	}
}