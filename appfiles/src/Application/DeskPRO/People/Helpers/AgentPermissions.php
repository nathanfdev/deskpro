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

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Figures out agent permissions
 */
class AgentPermissions implements \ArrayAccess, \Orb\Helper\ShortCallableInterface
{
	protected $person;

	protected $_allowed_ids = null;
	protected $_disallowed_ids = null;

	public function __construct(Entity\Person $person)
	{
		$this->person = $person;
	}

	public function getShortCallableNames()
	{
		return array(
			'getAgentPermissions' => '_getthis',
			'getDisallowedDepartments' => 'getDisallowedDepartments',
			'getAllowedDepartments' => 'getAllowedDepartments',
		);
	}

	// we use this because we implement arrayaccess
	// so the caller gets this, and can use it as an array.
	// So if the caller gets it through a another array access, it means
	// we support $whatever['thishelper']['thisobject'];
	public function _getthis() { return $this; }



	/**
	 * Check if the user is allowed to use a particular department
	 *
	 * @param int|Department $dep
	 * @return bool
	 */
	public function isDepartmentAllowed($dep)
	{
		if ($dep instanceof Entity\Department) {
			$dep = $dep['id'];
		}

		return in_array($dep, $this->getAllowedDepartments());
	}



	/**
	 * Get an array of departments the user isn't allowed to see
	 *
	 * @return array
	 */
	public function getDisallowedDepartments()
	{
		if ($this->_disallowed_ids !== null) return $this->_disallowed_ids;

		$all_ids = App::getEntityRepository('DeskPRO:Department')->getDepartmentIds();
		$allowed_ids = $this->getAllowedDepartments();

		$disallowed_ids = array_diff($all_ids, $allowed_ids);

		$this->_disallowed_ids = $disallowed_ids;

		return $this->_disallowed_ids;
	}



	/**
	 * Get an array of departments the user is allowed to see
	 *
	 * @return array
	 */
	public function getAllowedDepartments()
	{
		if ($this->_allowed_ids !== null) return $this->_allowed_ids;

		// TODO after we figure out how we're doing the ui around
		// permissions

		$this->_allowed_ids = App::getEntityRepository('DeskPRO:Department')->getDepartmentIds();

		return $this->_allowed_ids;
	}


	public function offsetExists($offset)
	{
		$o = array('allowed_dep_ids', 'disallowed_dep_ids');
		return in_array($offset, $o);
	}
	public function offsetGet($offset)
	{
		if ($offset == 'allowed_dep_ids') {
			return $this->getAllowedDepartments();
		} else {
			return $this->getDisallowedDepartments();
		}
	}
	public function offsetSet($offset, $value)
	{
		throw new \BadMethodCallException('offsetSet not supported');
	}
	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException('offsetUnset not supported');
	}
}
