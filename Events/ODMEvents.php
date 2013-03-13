<?php 
namespace RC\PHPCRMenuNodeFromRoutesBundle\Events;

use Doctrine\ODM\PHPCR\Event\PostFlushEventArgs;

class ODMEvents{
	
	
	
	public function postFlush(PostFlushEventArgs $event){
		var_dump('flushes');
	}
	
}