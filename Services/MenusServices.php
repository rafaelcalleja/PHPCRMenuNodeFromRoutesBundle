<?php
namespace RC\PHPCRMenuNodeFromRoutesBundle\Services;
use RC\PHPCRSeoBundle\Document\SeoNode;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Cmf\Bundle\MenuBundle\Document\MenuNode;
use Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuNode;

use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\EventManager;

use PHPCR\Util\NodeHelper;

class MenusServices {

	protected $dm, $locales;

	public function __construct(DocumentManager $dm,LocaleChooser $localeChooser,$locales ) {
		$this->dm = $dm->create($dm->getPhpcrSession(),  $dm->getConfiguration(), $dm->getEventManager() );
		$this->dm->setLocaleChooserStrategy($localeChooser);
		$this->locales = $locales;
	}
	
	public function createMenuRoot($menubase, $menuid){
		foreach($this->locales as $l){
			$parent = $this->dm->find(null, $menubase);
			
	
			if (!$parent) {
				NodeHelper::createPath($this->dm->getPhpcrSession(), $menubase);
				$parent = $this->dm->find(null, $menubase);
			}
			
			$menuitem = new MenuNode();
			$menuitem->setParent($parent);
			$menuitem->setName($menuid.'_'.$l);
			
			$this->dm->persist($menuitem);
		}
		
		$this->dm->flush();
		
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
		
		return $menuitem;
	}
	
	public function remove($source){
		$block = $this->dm->find(null, $source);
		if($block instanceof MenuNode){
			$this->dm->remove($block);
			$this->dm->flush();
		}
	}
	
	public function move_menu($source, $dest){
		$block = $this->dm->find(null, $source);
		$this->dm->move($block, $dest);
		$this->dm->flush($block);
	}
	
	public function updateMenu($menuid, $routeid, $oldnamem, $newtitle){
		
		$newroute = $this->dm->find(null, $routeid);
// 		$newtitle = $newroute->getRouteContent()->getTitle();
		$menu = $this->dm->find(null, $menuid);
		
		
		$menu->setName(basename($routeid));
		$menu->setLabel($newtitle);
		
		
		
		$this->fixUriException();
		$menu->setUri($newroute->getPattern());
		$this->dm->persist($menu);
		$this->dm->flush($menu);
		$this->updateChild($menu->getChildren(), count(explode('/', $menu->getId()))-1, count(explode('/', $menu->getUri()))-1);
		
		
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
		if(is_array($hijos) && count($hijos) > 0){
			foreach($hijos as $h){
				$ids = explode('/', $h->getId());
				$uris = explode('/', $h->getUri());
				$uris[$dest_pos] = $ids[$source_pos];
				$h->setUri(implode('/', $uris));
				$this->dm->persist($h);
				$this->dm->flush($h);
				$this->updateChild($h->getChildren(), $source_pos, $dest_pos);
				
			}
		}
		
	}
}
