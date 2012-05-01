<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;

use Orb\Util\Arrays;

class Departments extends AbstractLoader
{
	/**
	 * An array of categories allowed for real, that we get by computing
	 * inheritance.
	 * @var array
	 */
	protected $allowed_cats = array('tickets' => array(), 'chat' => array());

	protected function init()
	{
		$in = implode(',', $this->getUsergroupIds());
		$res = App::getDb()->fetchAll("
			SELECT department_id, app
			FROM department_permissions
			WHERE usergroup_id IN($in)
		");

		foreach ($res as $d) {
			$dep = App::getDataService('Department')->get($d['department_id']);

			$check = 'is_' . $d['app'] . '_enabled';
			if (!isset($dep->$check) || !$dep->$check) {
				continue;
			}

			$this->allowed_cats[$d['app']][$d['department_id']] = $d['department_id'];

			// With departments, if a child is allowed, then the parent is too since its just a wrapper
			if ($dep && $dep->parent) {
				$this->allowed_cats[$d['app']][$dep->parent->getId()] = $dep->parent->getId();
			}
		}

		foreach ($this->allowed_cats as &$_x) {
			$_x = array_unique($_x);
		}
	}

	/**
	 * Is a dep allowed?
	 *
	 * @return bool
	 */
	public function isAllowed($id, $app)
	{
		return isset($this->allowed_cats[$app][$id]);
	}


	/**
	 * Get an array of all allowed categories.
	 *
	 * @return array
	 */
	public function getAllowed($app)
	{
		return $this->allowed_cats[$app];
	}


	/**
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	protected function serializeData()
	{
		return array(
			'allowed_cats'    => $this->allowed_cats,
		);
	}


	/**
	 * Initialize this object with an array of saved data
	 *
	 * @param array $data
	 */
	protected function unserializeData(array $data)
	{
		$this->allowed_cats     = $data['allowed_cats'];
	}
}
