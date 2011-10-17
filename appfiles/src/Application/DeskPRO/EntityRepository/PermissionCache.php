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

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class PermissionCache extends EntityRepository
{
	public function loadPermissionTypes($usergroup_key, $person_id, array $types)
	{
		// A simple filter to make sure only valid names are included
		$types = array_filter($types, function($var) {
			return !preg_match('#[^a-zA-Z0-9_]#', $var);
		});

		if (!$types) {
			return array();
		}

		$key = $usergroup_key;

		if ($person_id) {
			$key .= ".$person_id";
		}

		$types = '\'' . implode('\',\'', $types) . '\'';

		$caches = $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:PermissionCache c
			WHERE c.name IN ($types) AND (c.usergroup_key = ?1 OR c.usergroup_key = ?2)
		")->setParameter(1, $usergroup_key)
		  ->setParameter(2, $key)
		  ->getResult();

		return $caches;
	}
}
