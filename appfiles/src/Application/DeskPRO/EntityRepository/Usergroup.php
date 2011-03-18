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

namespace Application\DeskPRO\EntityRepository;

use \Orb\Util\Arrays;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class Usergroup extends EntityRepository
{
	protected $_usergroup_names = null;
	protected $_agent_usergroup_names = null;



	/**
	 * Get an array of id=>name for usergroups.
	 *
	 * @return array
	 */
	public function getUsergroupNames($for_ids = null)
	{
		if ($this->_usergroup_names === null) {

            $db = App::getDb();
            $this->_usergroup_names = $db->fetchAllKeyValue("
                SELECT id, title
                FROM usergroups
                WHERE is_agent_group = 0
                ORDER BY title ASC
            ");
        }

        if ($for_ids === null) {
            return $this->_usergroup_names;
        }

        $ret = array();
        foreach ($for_ids as $id) {
            $ret[$id] = $this->_usergroup_names[$id];
        }

        return $ret;
	}



	/**
	 * Get an array of id=>name for agent usergroups.
	 *
	 * @return array
	 */
	public function getAgentUsergroupNames()
	{
		if ($this->_agent_usergroup_names !== null) return $this->_agent_usergroup_names;

		$db = App::getDb();
		$this->_agent_usergroup_names = $db->fetchAllKeyValue("
			SELECT id, title
			FROM usergroups
			WHERE is_agent_group = 0
			ORDER BY title ASC
		");

		return $this->_agent_usergroup_names;
	}
}