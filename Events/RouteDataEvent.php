<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;

class RouteDataEvent extends Event
{
	protected $document, $dm;
	

	public function __construct($event){
		$this->document = $event->getDocument();
		$this->dm = $event->getDocumentManager();
	}
	
	public function getLocale(){
		return $this->document->getDefault('_locale');
	}
	
	public function getPath(){
		return $this->document->getPattern();
		
	}
	
	public function getId(){
		return $this->document->getId();
	}
	
	public function getLabel(){
		if((method_exists($this->document->getRouteContent(), 'getId'))){
			$translation = $this->dm->findTranslation(get_class($this->document->getRouteContent()), $this->document->getRouteContent()->getId(), $this->getLocale());
			if(method_exists($translation, 'getTitle')) {
				return $translation->getTitle();
			}
		}
		
		return false;
	}
	
	
	public function getDocument(){
		return $this->document;
	}
}