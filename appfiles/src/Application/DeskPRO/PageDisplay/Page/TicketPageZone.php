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
use Application\DeskPRO\Entity\TicketPageDisplay;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Department;

/**
 * TicketPage's have a zone context (user, agent) and a department context.
 *
 * The person context is used in criteria for rule matching only.
 */
class TicketPageZone extends BasicPage implements PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	/**
	 * The department context for all these pagedisplays
	 * @var \Application\DeskPRO\Entity\Department
	 */
	protected $department;

	/**
	 * The zone we're in
	 */
	protected $zone;

	/**
	 * @param string $zone The zone (one of TicketPageDisplay::ZONE_*)
	 * @param Department $department The department context
	 */
	public function __construct($zone, Department $department)
	{
		$this->zone = $zone;
		$this->department = $department;
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
		if (!($page_display instanceof TicketPageDisplay)) {
			throw new \InvalidArgumentException('You can only add `TicketPageDisplay` types');
		}

		if ($this->zone != $page_display['zone']) {
			throw new \InvalidArgumentException('Invalid zone context. Must be: ' . $this->zone);
		}
		if ($this->department['id'] != $page_display->department['id']) {
			throw new \InvalidArgumentException('Invalid department context. Must be: ' . $this->department['id']);
		}

		parent::addPageDisplay($page_display);
	}


	/**
	 * Reads page displays from the TicketPageDisplay entity repository
	 * @return void
	 */
	public function addPageDisplaysFromDb()
	{
		$page_displays = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getFromZone($this->zone, $this->department);
		$this->addPageDisplays($page_displays);
	}

	
	/**
	 * Get the zone
	 * 
	 * @return string
	 */
	public function getZone()
	{
		return $this->zone;
	}
	

	/**
	 * Get the department
	 * 
	 * @return \Application\DeskPRO\Entity\Department
	 */
	public function getDepartment()
	{
		return $this->department;
	}
}