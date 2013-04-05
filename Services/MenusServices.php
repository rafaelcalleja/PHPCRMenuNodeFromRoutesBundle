<?php
namespace RC\PHPCRMenuNodeFromRoutesBundle\Services;
use RC\PHPCRSeoBundle\Document\SeoNode;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Symfony\Cmf\Bundle\MenuBundle\Document\MenuNode;
use Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuNode;

use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\EventManager;

use PHPCR\Util\NodeHelper;
use PHPCR\Util\PathHelper;

class MenusServices {

	/**
	 * @var String allowed locales
	 */
	protected $locales;
	
	/**
	 * @var \Doctrine\ODM\PHPCR\DocumentManager
	 */
	protected $dm;
	
	
	/**
	 * @var null|LoggerInterface
	 */
	protected $logger;
	

	public function __construct(DocumentManager $dm,LocaleChooser $localeChooser, $locales, LoggerInterface $logger = null ) {
		$this->dm = $dm->create($dm->getPhpcrSession(),  $dm->getConfiguration(), $dm->getEventManager() );
		$this->dm->setLocaleChooserStrategy($localeChooser);
		$this->locales = $locales;
		$this->logger = $logger;
	}
	
	public function createMenuRoot($menubase, $menuid){
		
		if( empty($this->locales) && $this->logger){
			$this->logger->debug("Locales is not set, no root menu was create");
		}
		
		try {
			
			foreach($this->locales as $l){
				$parent = $this->dm->find(null, $menubase);
				
		
				if (!$parent) {
					NodeHelper::createPath($this->dm->getPhpcrSession(), $menubase);
					$parent = $this->dm->find(null, $menubase);
				}
				
				$exists = $this->dm->find(null, "$menubase/$menuid".'_'.$l);
				if($exists)continue;
				
				$menuitem = new MenuNode();
				$menuitem->setParent($parent);
				$menuitem->setName($menuid.'_'.$l);
				$this->dm->persist($menuitem);
			}
			
			$this->dm->flush();
			if( $this->logger ){
				$message = sprintf('Auto Menu root was created for locales %s, %s/%s', $locales, $menubase, $menuid);
				$this->logger->info($message);
			}
			
		}catch(\Exception $e){
			
			if( $this->logger ){
				$message = sprintf("The class %s, The method %s, Exception %s", __CLASS__, __FUNCTION__, $e->getMessage() );
				$this->logger->error($message);
			}
			
		}
		
	}

	
	public function createMenuItem($basename, $menuid, $label, $uri, $attributes = array()) {
		$parent = $this->dm->find(null, $basename);
		

		if (!$parent) {
			NodeHelper::createPath($this->dm->getPhpcrSession(), $basename);
			$parent = $this->dm->find(null, $basename);
			
		}
		
		
		
		$menuitem = is_array($label) ? new MultilangMenuNode() : new MenuNode();

		$this->fixUriException();

		$menuitem->setParent($parent);
		$menuitem->setName($menuid);

		if ($attributes != array()) {
			$menuitem->setAttributes($attributes);
		}
		
		//$this->dm->persist($menuitem);
		

		if (is_array($label)) {
			foreach ($label as $locale => $l) {
				
					$menuitem->setUri($uri[$locale]);
					$menuitem->setLabel($l);
					$this->dm->bindTranslation($menuitem, $locale);
			}
		} else {
			$menuitem->setUri($uri);
			$menuitem->setLabel($label);
		}


		$this->dm->persist($menuitem);
		$this->dm->flush($menuitem);
		
		if( $this->logger ){
			$message = sprintf('Menu Node was created for basename/%s label:%s, uri %s', $basename, $menuid, $label, $uri);
			$this->logger->info($message);
		}
		
		return $menuitem;
	}
	
	public function remove($source){
		
		try{
		
			$block = $this->dm->find(null, $source);
			
			if( !$block && $this->logger ){
				$message = sprintf("Trying to remove non-existent source %s, class %s, method %s ", $source, __CLASS__, __FUNCTION__);
				$this->logger->error($message);
			}
			
			if($block instanceof MenuNode){
				$this->dm->remove($block);
				$this->dm->flush();
				
				if( $this->logger ){
					$message = sprintf('Menu Node was removed SOURCE: %s', $source);
					$this->logger->info($message);
				}
				
			}
			
		}catch(\Exception $e){
			
			if( $this->logger ){
				$message = sprintf("The class %s, The method %s, Exception %s", __CLASS__, __FUNCTION__, $e->getMessage() );
				$this->logger->error($message);
			}
			
		}
		
	}
	
	public function move_menu($source, $dest){
		try{
			
			$block = $this->dm->find(null, $source);
			
			if( !$block && $this->logger ){
				$message = sprintf("Trying to move non-existent source %s, class %s, method %s ", $source, __CLASS__, __FUNCTION__);
				$this->logger->error($message);
			}
			
			$this->dm->move($block, $dest);
			$this->dm->flush($block);
			
			if( $this->logger ){
				$message = sprintf('Menu Node was moved  From %s To %s ', $source, $dest);
				$this->logger->info($message);
			}
			
		}catch(\Exception $e){
			
			if( $this->logger ){
				$message = sprintf("The class %s, The method %s, Exception %s", __CLASS__, __FUNCTION__, $e->getMessage() );
				$this->logger->error($message);
			}
			
		}
	}
	
	public function updateMenu($menuid, $routeid, $oldnamem, $newtitle){
		
		try{
			$newroute = $this->dm->find(null, $routeid);
	// 		$newtitle = $newroute->getRouteContent()->getTitle();
			$menu = $this->dm->find(null, $menuid);
			
			
			$menu->setName(basename($routeid));
			$menu->setLabel($newtitle);
			
			
			
			$this->fixUriException();
			$menu->setUri($newroute->getPattern());
			$this->dm->persist($menu);
			$this->dm->flush($menu);
			
			if( $this->logger ){
				$message = sprintf('Menu was updated %s route: %s, new label: %s, new Uri: %s', $menuid, $routeid, $newtitle, $newroute->getPattern());
				$this->logger->info($message);
			}
			
			$this->updateChild($menu->getChildren(), count(explode('/', $menu->getId()))-1, count(explode('/', $menu->getUri()))-1);
			
		}catch(\Exception $e){
				
			if( $this->logger ){
				$message = sprintf("The class %s, The method %s, Exception %s", __CLASS__, __FUNCTION__, $e->getMessage() );
				$this->logger->error($message);
			}
				
		}
			
		
		
	}
	
	public function reorderMenu($old, $new, $nodepath){
		try{
			
			if( $this->logger && $new === $old){
				$this->logger->info('Nothing to reorder');
			}
			
			$reorder = NodeHelper::calculateOrderBefore($old, $new);
			$node = $this->dm->getPhpcrSession()->getNode(PathHelper::absolutizePath($nodepath, '', false, false));
			foreach ($reorder as $srcChildRelPath => $destChildRelPath) {
				$node->orderBefore($srcChildRelPath, $destChildRelPath);
			}
			 
			$this->dm->flush();
			
			if( $this->logger ){
				$message = sprintf('Menu was reorder Path: %s', $nodepath );
				$this->logger->info($message);
			}
			
		}catch(\Exception $e){
				
			if( $this->logger ){
				$message = sprintf("The class %s, The method %s, Exception %s", __CLASS__, __FUNCTION__, $e->getMessage() );
				$this->logger->error($message);
			}
				
		}
	}
	
	private function fixUriException(){
		$metadata = $this->dm->getClassMetadata("Symfony\Cmf\Bundle\MenuBundle\Document\MenuNode");
		$multi = $this->dm->getClassMetadata("Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuNode");
		
		if ($metadata->hasField('uri')) {
			$maps = array("fieldName" => "uri",
					"type" => "String",
					"translated" => false,
					"name" => "uri",
					"multivalue" => false,
					"assoc" => null);
		
			$metadata->mapField($maps, $multi);
			$metadata->mapField($maps, $metadata);
		
		}
	}
	
	private function updateChild($hijos, $source_pos, $dest_pos){
		try{
			if(is_array($hijos) && count($hijos) > 0){
				foreach($hijos as $h){
					$ids = explode('/', $h->getId());
					$uris = explode('/', $h->getUri());
					$uris[$dest_pos] = $ids[$source_pos];
					$h->setUri(implode('/', $uris));
					$this->dm->persist($h);
					$this->dm->flush($h);
					
					if( $this->logger ){
						$message = sprintf('Child Menu was updated new Uri: %s', implode('/', $uris));
						$this->logger->info($message);
					}
					
					$this->updateChild($h->getChildren(), $source_pos, $dest_pos);
					
				}
			}
		}catch(\Exception $e){
		
			if( $this->logger ){
				$message = sprintf("The class %s, The method %s, Exception %s, source %s, dest %s", __CLASS__, __FUNCTION__, $e->getMessage(), $source_pos, $dest_pos );
				$this->logger->error($message);
			}
		
		}
		
	}
}
