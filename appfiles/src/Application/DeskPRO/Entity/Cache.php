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

use \Application\DeskPRO\App;

use \Orb\Util\Strings;

/**
 * A simple DB table cache for k=>v
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Cache")
 * @ORM_Mapping\Table(name="cache")
 */
class Cache extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="id", type="string", length=100)
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_expire",type="datetime", nullable=true)
	 */
	protected $date_expire;

	public function setData($data)
	{
		if (!is_array($data)) {
			$this->data = array('VALUE' => $data);
		}
	}

	public function getData()
	{
		if (isset($this->data['VALUE'])) {
			return $this->data['VALUE'];
		}

		return $this->data;
	}
}