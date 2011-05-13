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

/**
 * A Page wraps up handling of sections and their items.
 *
 * Descriptions for these pages are stored in `PageDisplay` tables. For example, `TicketPageDisplay`.
 * 
 * = Data Structure =
 *
 * The data in a PageDisplay structure is as follows:
 *
 * array(
 *     // any page-specific information
 *     'items' => array(
 *         array(
 *             'type' => 'xxx',
 *              // any type-specific information
 *         )
 *     )
 * );
 */
class BasicPage
{
	const SECTION_DEFAULT = 'default';

	/**
	 * array[section] = page_display
	 * @var \Application\DeskPRO\Entity\PageDisplayAbstract[]
	 */
	protected $page_displays = array();


	/**
	 * @param \Application\DeskPRO\Entity\PageDisplayAbstract $page_display
	 * @return void
	 */
	public function addPageDisplay(PageDisplayAbstract $page_display)
	{
		$section = $page_display['section'];
		$this->page_displays[$section] = $page_display;
	}


	/**
	 * @param \Application\DeskPRO\Entity\PageDisplayAbstract[] $page_displays
	 * @return void
	 */
	public function addPageDisplays(array $page_displays)
	{
		foreach ($page_displays as $page_display) {
			$this->addPageDisplay($page_display);
		}
	}


	/**
	 * Check to see if a section has been set
	 *
	 * @param string $section
	 * @return bool
	 */
	public function hasPageDisplay($section)
	{
		return isset($this->page_displays[$section]);
	}

	
	/**
	 * Returns a section
	 *
	 * @param  $section
	 * @return \Application\DeskPRO\Entity\PageDisplayAbstract[]|null
	 */
	public function getPageDisplay($section)
	{
		if (!isset($this->page_displays[$section])) {
			return null;
		}

		return $this->page_displays[$section];
	}

	
	/**
	 * Get all page displays
	 * 
	 * @return \Application\DeskPRO\Entity\PageDisplayAbstract[]
	 */
	public function getPageDisplays()
	{
		return $this->page_displays;
	}
}