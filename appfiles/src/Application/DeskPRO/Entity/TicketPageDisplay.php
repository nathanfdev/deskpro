<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Description for a section within the ticket page.
 *
 * @see \Application\DeskPRO\PageDisplay\Zone\BasicZone
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketPageDisplay")
 * @orm:Table(name="ticket_page_display")
 */
class TicketPageDisplay extends PageDisplayAbstract
{
	const ZONE_AGENT = 'agent';
	const ZONE_USER  = 'user';

	/**
	 * Where this element description applies. Examples:
	 * - agent
	 * - user
	 *
	 * @var string
	 * @orm:Column(name="zone", type="string", length=50)
	 */
	protected $zone;
	
	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * Options/flags you can enable. For example "enable_captcha". These aren't actual
	 * display elements, but stored here for convenience.
	 *
	 * Generally these are saved in the 'default' section.
	 *
	 * @var array
	 * @orm:Column(name="options", type="array")
	 */
	protected $options = array();


	/**
	 * Set the department id
	 *
	 * @param int $id
	 */
	public function setDepartmentId($id)
	{
		if (!$id) {
			$this->department = 0;
		} else {
			$this->department = App::getEntityRepository('DeskPRO:Department')->find($id);
		}
	}

	
	/**
	 * Get the department id
	 *
	 * @return int
	 */
	public function getDepartmentId()
	{
		if (!$this->department) {
			return 0;
		}

		return $this->department['id'];
	}


	/**
	 * Get an option
	 *
	 * @param  $name
	 * @param null $default
	 * @return array|null
	 */
	public function getOption($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	
	/**
	 * Set an option
	 *
	 * @param  $name
	 * @param  $value
	 * @return void
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
	}
}