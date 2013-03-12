<?php
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;



use Doctrine\ODM\PHPCR\Event\MoveEventArgs;
use Doctrine\ODM\PHPCR\Event\PostFlushEventArgs;
use Doctrine\ODM\PHPCR\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping\PreFlush;
use Doctrine\ODM\PHPCR\Event\OnFlushEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Assetic\Exception\Exception;
use Doctrine\ORM\EntityNotFoundException;
use CMF\DnoiseBundle\DataFixtures\PHPCR\RouteLoader;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;
use Symfony\Component\DependencyInjection\Container;
use CMF\CodespaWebBundle\Document\ProjectBlock;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;
use PHPCR\ItemExistsException;

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
	
	public function postLoad(LifecycleEventArgs $event){
		$document = $event->getDocument();
		
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			//$newevent = new RouteMoveEventsData($event);
			//var_dump($document->getId());
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
// 		if(method_exists($document, 'getTitle')){
// 			var_dump($document->getTitle(), 'existo');
// 		}
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			$newevent = new RouteDataEvent($event);
			$this->dispatcher->dispatch(RouteEvents::ROUTE_PRE_EDITED, $newevent);
			//
			//var_dump($document->getPattern(), $document->getDefault('_locale'));
			/*if($event->getDocumentManager()->isDocumentTranslatable($document)){
			 var_dump($event->getDocumentManager()->getLocalesFor($document));
			}*/
		}
	}
	
	public function postUpdate(LifecycleEventArgs $event){
		$document = $event->getDocument();
		
// 		if(method_exists($document, 'getTitle')){
// 			var_dump($document->getTitle(), 'existoasd', get_class($document));
// 		}
		if( $document instanceof \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route){
			//var_dump(get_class($document));
			$newevent = new RouteDataEvent($event);
			$this->dispatcher->dispatch(RouteEvents::ROUTE_EDITED, $newevent);
			
			//
			//var_dump($document->getPattern(), $document->getDefault('_locale'));
			/*if($event->getDocumentManager()->isDocumentTranslatable($document)){
			 var_dump($event->getDocumentManager()->getLocalesFor($document));
			}*/
		}
	
//  		$R= Request::createFromGlobals();
	
// 		if (($event->getDocument() instanceof ProjectBlock) && $R->getRequestUri())
// 		{
// 			$X=$event->getDocument();
//  			$metadata = $event->getDocumentManager()->getClassMetadata(get_class($X));
// 	 		foreach ($event->getDocumentManager()->getLocalesFor($X) as $Locale)
//  			{
//  				$event->getDocumentManager()->getUnitOfWork()->doLoadTranslation($X,$metadata,$Locale);
//  				$this->routes[]=$this->rl->updateRoute($event->getDocument(),$event->getDocument()->getTitle(),$Locale);
 				
 				
//  			}
	//}
	
	}	
	
}