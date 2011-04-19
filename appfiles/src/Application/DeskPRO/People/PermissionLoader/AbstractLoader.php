<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\PermissionLoader;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\Person;

use \Orb\Util\Arrays;

/**
 * A permission loader knows how to load permissions for a thing.
 */
abstract class AbstractLoader implements \Serializable
{
	/**
	 * @var int[]
	 */
	protected $usergroup_ids;

	/**
	 * @param int[] $usergroup_ids
	 */
	public function __construct(array $usergroup_ids)
	{
		$this->usergroup_ids = $usergroup_ids;
		sort($this->usergroup_ids, \SORT_NUMERIC);

		$this->init();
	}

	protected function init() {}


	
	/**
	 * Get the usergroup IDs represented by the loaded permissions
	 *
	 * @return array
	 */
	public function getUsergroupIds()
	{
		return $this->usergroup_ids;
	}



	/**
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	abstract protected function serializeData();

	public function serialize()
	{
		$data = $this->serializeData();
		$data['usergroup_ids'] = $this->usergroup_ids;

		return serialize($data);
	}

	/**
	 * Initialize this object with an array of saved data
	 * 
	 * @param array $data
	 */
	abstract protected function unserializeData(array $data);
	
	public function unserialize($data)
	{
		$data = unserialize($data);

		$this->usergroup_ids = $data['usergroup_ids'];
		$this->unserializeData($data);
	}
}