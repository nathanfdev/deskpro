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

use Application\DeskPRO\Entity\PageDisplayAbstract;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Controller\AbstractController;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\PageDisplay\Item\Portal\PortalItemAbstract;

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
	 * @var \Application\DeskPRO\Controller\AbstractController
	 */
	protected $controller;

	/**
	 * The user who is viewing the item
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	public function __construct(AbstractController $controller, Person $person_context)
	{
		$this->controller = $controller;
		$this->person_context = $person_context;
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

		$obj = new $type_class($section, $item_info, $this->controller, $this->person_context);

		return $obj;
	}

	
	/**
	 * Get an array of all CSS assets used by all portal items
	 *
	 * @return array
	 */
	public function getCssAssets()
	{
		$assets = array();
		
		foreach ($this->page_display_items as $section_items) {
			foreach ($section_items as $item) {
				$assets = array_merge($assets, $item->getCssAssets());
			}
		}

		return $assets;
	}


	/**
	 * Get an array of all JS assets used by all portal items
	 *
	 * @return array
	 */
	public function getJsAssets()
	{
		$assets = array();

		foreach ($this->page_display_items as $section_items) {
			foreach ($section_items as $item) {
				$assets = array_merge($assets, $item->getJsAssets());
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
		if (!isset($this->page_display_items[$section])) {
			return '';
		}

		$html = array();
		foreach ($this->page_display_items[$section] as $item) {
			$html[] = $item->getHtml();
		}

		return implode("\n\n", $html);
	}


	public function __get($section)
	{
		if ($section == 'js_assets') {
			return $this->getJsAssets();
		} elseif ($section == 'css_assets') {
			return $this->getCssAssets();
		} else {
			return $this->getSectionHtml($section);
		}
	}

	public function __isset($section)
	{
		return (isset($this->page_display_items[$section]) OR $section == 'js_assets' OR $section == 'css_assets');
	}
}