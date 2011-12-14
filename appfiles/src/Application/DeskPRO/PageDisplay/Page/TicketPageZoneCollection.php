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

use Application\DeskPRO\People\PersonContextInterface;

use Application\DeskPRO\Entity\PageDisplayAbstract;
use Application\DeskPRO\Entity\TicketPageDisplay;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Department;

use Orb\Util\Arrays;

/**
 * This is a collection of TicketPageZone's meant to group a bunch of layouts
 * under one object. All pages must be the same zone, but the department changes.
 */
class TicketPageZoneCollection implements PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	/**
	 * @var string
	 */
	protected $zone;

	/**
	 * department_pages[dep_id] = array(ticketpagezone)
	 * @var \Application\DeskPRO\PageDisplay\Page\TicketPageZone
	 */
	protected $department_pages = array();

	/**
	 * @param string $zone
	 */
	public function __construct($zone)
	{
		$this->zone = $zone;
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
	 * Read all TIcketPageDisplay records from the database, initialize TicketPageZone's,
	 * and then add them to this collection.
	 */
	public function addPagesFromDb()
	{
		$dep_page_displays = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getFromZone($this->zone);
		$dep_page_displays = Arrays::groupItems($dep_page_displays, 'department_id');

		$default_page = null;

		foreach ($dep_page_displays as $dep_id => $page_displays) {
			$dep = null;
			if ($dep_id) {
				$dep = App::findEntity('DeskPRO:Department', $dep_id);
				if (!$dep) {
					continue;
				}
			}

			$ticket_page_zone = new TicketPageZone($this->zone, $dep);
			$ticket_page_zone->addPageDisplays($page_displays);
			$this->addPage($ticket_page_zone);
		}
	}


	/**
	 * @param \Application\DeskPRO\PageDisplay\Page\TicketPageZone $page
	 * @return void
	 */
	public function addPage(TicketPageZone $page)
	{
		if ($page->getZone() != $this->zone) {
			throw new \InvalidArgumentException('Invalid zone context. Must be: ' . $this->zone);
		}

		$dep_id = $page->getDepartment() ? $page->getDepartment()->getId() : 0;
		$this->department_pages[$dep_id] = $page;
	}


	/**
	 * Add an array of pages at once
	 *
	 * @param \Application\DeskPRO\PageDisplay\Page\TicketPageZone[] $pages
	 */
	public function addPages(array $pages)
	{
		foreach ($pages as $page) {
			$this->addPage($page);
		}
	}


	/**
	 * Check if we havea  zone set for a department
	 *
	 * @param int|Department $department
	 * @return bool
	 */
	public function hasPage($department)
	{
		if (is_object($department)) $department = $department['id'];

		return isset($this->department_pages[$department]);
	}


	/**
	 * Get the page for a department
	 *
	 * @param int|Department $department
	 * @return array|null
	 */
	public function getPage($department)
	{
		if (is_object($department)) $department = $department['id'];

		if (!isset($this->department_pages[$department])) {
			return null;
		}

		return $this->department_pages[$department];
	}


	public function compileJs()
	{
		$part = array();
		foreach ($this->department_pages as $dep_id => $page_zone) {
			$part[] = "$dep_id: " . $page_zone->compileJs();
		}

		$part = "{\n" . implode(",\n", $part) . "\n}";
		return $part;
	}
}
