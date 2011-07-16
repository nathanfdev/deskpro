<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Page;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\PageDisplayAbstract;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\PageDisplay\Item\Portal\PortalItemAbstract;
use Application\DeskPRO\PageDisplay\Item\Portal\CacheableItem;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Orb\Util\Strings;

class PortalPage extends BasicPage implements PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\PageDisplay\Item\Portal\PortalItemAbstract[]
	 */
	protected $page_display_items = array();

	/**
	 * The controller requesting the portal item
	 *
	 * @var Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * The user who is viewing the item
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	/**
	 * If provided, this will lazy-load a PortalPageDisplay for a section if it doesnt exist
	 * yet in this object.
	 * @var callback
	 */
	protected $lazy_loader = null;

	public function __construct(ContainerInterface $container, Person $person_context)
	{
		$this->container = $container;
		$this->person_context = $person_context;

		// TODO: Hard-coded until we get editor working
		$pagetop_pagedisplay = new PortalPageDisplay();
		$pagetop_pagedisplay['section'] = PortalPageDisplay::SECTION_PAGETOP;
		$pagetop_pagedisplay['data'] = array(
			array(
				'type' => 'omni_search',
			),
			array(
				'type' => 'notifications',
			),
		);

		$content_pagedisplay = new PortalPageDisplay();
		$content_pagedisplay['section'] = PortalPageDisplay::SECTION_PORTAL;
		$content_pagedisplay['data'] = array(
			array(
				'type' => 'news'
			),
			array(
				'type' => 'downloads'
			),
			array(
				'type' => 'kb'
			),
			array(
				'type' => 'ideas'
			),
		);

		$sidebar_pagedisplay = new PortalPageDisplay();
		$sidebar_pagedisplay['section'] = PortalPageDisplay::SECTION_SIDEBAR;
		$sidebar_pagedisplay['data'] = array(
			array(
				'type' => 'userinfo',
			),
			array(
				'type' => 'contact',
			),
			array(
				'type' => 'news'
			),
			array(
				'type' => 'downloads'
			),
			array(
				'type' => 'ideas'
			),
			array(
				'type' => 'template',
				'tpl' => 'UserBundle:Portal:staff-sidebar.html.twig'
			),
			array(
				'type' => 'twitter',
				'twitter_name' => 'deskpro',
			),
		);

		$this->addPageDisplay($pagetop_pagedisplay);
		$this->addPageDisplay($content_pagedisplay);
		$this->addPageDisplay($sidebar_pagedisplay);
	}
	

	/**
	 * @param callback $lazy_loader
	 * @return void
	 */
	public function setLazyLoader($lazy_loader)
	{
		$this->lazy_loader = $lazy_loader;
	}


	protected function _loadSection($section)
	{
		if ($this->lazy_loader AND !isset($this->page_displays[$section])) {
			$lazy_loader = $this->lazy_loader;
			$page_display = $lazy_loader($section, $this);
			if ($page_display) {
				$this->addPageDisplay($page_display);
			}
		}
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return void
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	/**
	 * @param \Application\DeskPRO\Entity\PageDisplayAbstract $page_display
	 * @return void
	 */
	public function addPageDisplay(PageDisplayAbstract $page_display)
	{
		parent::addPageDisplay($page_display);
		$this->_initItems($page_display);
	}
	

	/**
	 * Init all items defined in the PortalPageDisplay and add it to
	 * this object.
	 *
	 * @param \Application\DeskPRO\Entity\PortalPageDisplay $page_display
	 * @return void
	 */
	protected function _initItems(PortalPageDisplay $page_display)
	{
		$section = $page_display['section'];
		if (!isset($this->page_display_items[$section])) {
			$this->page_display_items[$section] = array();
		}

		$data = $page_display['data'];

		foreach ($data as $item_info) {
			$this->page_display_items[$section][] = $this->_createPortalItem($section, $item_info);
		}
	}
	

	/**
	 * Creates a PortalItem object given the item info array
	 * 
	 * @param $section
	 * @param array $item_info
	 * @return \Application\DeskPRO\PageDisplay\Item\Portal\PortalItemAbstract
	 */
	public function _createPortalItem($section, array $item_info)
	{
		$type = $item_info['type'];
		if (strpos($type, '\\') === false) {
			$type_class = ucfirst(Strings::underscoreToCamelCase($type));
			$type_class = "Application\\DeskPRO\\PageDisplay\\Item\\Portal\\$type_class";
		} else {
			$type_class = $type;
		}

		$obj = new $type_class($section, $item_info, $this->container, $this->person_context);

		return $obj;
	}

	
	/**
	 * Get an array of all CSS assets used by all portal items
	 *
	 * @return array
	 */
	public function getCssAssets($sections)
	{
		if ($sections == 'all') {
			$sections = array_keys($this->page_displays);
		} else {
			$sections = (array)$sections;
		}

		$assets = array();

		foreach ($sections as $section) {
			$this->_loadSection($section);
			if (isset($this->page_display_items[$section])) {
				foreach ($this->page_display_items[$section] as $item) {
					$assets = array_merge($assets, $item->getCssAssets());
				}
			}
		}

		return $assets;
	}


	/**
	 * Get an array of all JS assets used by all portal items
	 *
	 * @return array
	 */
	public function getJsAssets($sections)
	{
		if ($sections == 'all') {
			$sections = array_keys($this->page_displays);
		} else {
			$sections = (array)$sections;
		}

		$assets = array();

		foreach ($sections as $section) {
			$this->_loadSection($section);
			if (isset($this->page_display_items[$section])) {
				foreach ($this->page_display_items[$section] as $item) {
					$assets = array_merge($assets, $item->getJsAssets());
				}
			}
		}

		return $assets;
	}


	/**
	 * Get the renderable HTML for a section.
	 *
	 * @param $section
	 * @return string
	 */
	public function getSectionHtml($section)
	{
		$this->_loadSection($section);
		
		if (!isset($this->page_display_items[$section])) {
			return '';
		}

		$cache = App::getCache('portal');

		$html = array();
		foreach ($this->page_display_items[$section] as $item) {
			$block_html = null;
			$cache_info = false;

			if ($item instanceof CacheableItem) {
				$cache_info = $item->getCacheOptions();
			}

			if ($cache_info) {

				if (!is_array($cache_info)) {
					$cache_info = array();
				}

				if (empty($cache_info['lifetime'])) $cache_info['lifetime'] = false;
				if (empty($cache_info['tags'])) $cache_info['tags'] = array();

				$cache_id = "portal_{$section}_" . str_replace('\\', '', get_class($item));
				$cache_lifetime = null;

				if (!isset($cache_info['user_indifferent']) OR !$cache_info['user_indifferent']) {
					$cache_id .= '_' . $this->person_context->getUsergroupSetKey();
				}

				if (($block_html = $cache->load($cache_id)) === false) {
					$block_html = $item->getHtml();
					$cache->save($block_html, $cache_id, $cache_info['tags'], $cache_info['lifetime']);
				}
				
			} else {
				$block_html = $item->getHtml();
			}

			if ($block_html) {
				$html[] = $block_html;
			}
		}

		return implode("\n\n", $html);
	}
}