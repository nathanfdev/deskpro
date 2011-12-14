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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\Entity;

/**
 * Description for a section within the ticket page.
 *
 * @see \Application\DeskPRO\PageDisplay\Zone\BasicZone
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketPageDisplay")
 * @ORM_Mapping\Table(name="ticket_page_display")
 */
class TicketPageDisplay extends PageDisplayAbstract
{
	const ZONE_AGENT = 'agent';
	const ZONE_USER  = 'user';

	const SECTION_DEFAULT    = 'default';

	/**
	 * Where this element description applies. Examples:
	 * - agent
	 * - user
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="zone", type="string", length=50)
	 */
	protected $zone;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @ORM_Mapping\ManyToOne(targetEntity="Department")
	 * @ORM_Mapping\JoinColumn(name="department_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $department = null;

	/**
	 * Options/flags you can enable. For example "enable_captcha". These aren't actual
	 * display elements, but stored here for convenience.
	 *
	 * Generally these are saved in the 'default' section.
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="options", type="array")
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


	public function setData(array $data)
	{
		$d = array();

		foreach ($data as $k => $item_data) {
			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $item_data['id'], $m)) {
				$item_data['field_type'] = $m[1];
				$item_data['field_id'] = $m[2];
			} else {
				$item_data['field_type'] = $item_data['id'];
			}

			$d[$k] = $item_data;
		}

		$this->setModelField('data', $d);
	}
}
