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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class Person extends EntityRepository
{
	protected $_agent_names = null;

	protected function _loadAgentNames()
	{
		if ($this->_agent_names !== null) return;

		$db = App::getDb();
		$this->_agent_names = $db->fetchAllKeyValue("
			SELECT id, CONCAT_WS(' ', first_name, last_name) AS full_name
			FROM people
			WHERE is_agent = 1
			ORDER BY full_name ASC
		");
	}

	public function getAgentNames($for_ids = null)
	{
		$this->_loadAgentNames();

		if ($for_ids === null) {
			return $this->_agent_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_agent_names[$id])) {
				$ret[] = $this->_agent_names[$id];
			}
		}

		return $ret;
	}



	/**
	 * Find a person by their email address.
	 *
	 * @param string $email
	 * @return Person
	 */
	public function findOneByEmail($email)
	{
		try {
			$person = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:Person p
				LEFT JOIN p.emails e
				WHERE e.email = ?1
				ORDER BY p.id ASC
			")->setParameter(1, $email)->setMaxResults(1)->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

		return $person;
	}
}