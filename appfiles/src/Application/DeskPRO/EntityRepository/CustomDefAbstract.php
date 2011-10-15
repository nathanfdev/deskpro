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

class CustomDefAbstract extends EntityRepository
{
	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			ORDER BY f.display_order ASC
		")->execute();
	}

	public function getEnabledFields()
	{
		return $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			WHERE f.is_enabled = true
			ORDER BY f.display_order ASC
		")->execute();
	}

	/**
	 * @return array
	 */
	public function getTopFields()
	{
		return $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			WHERE f.parent IS NULL
			ORDER BY f.display_order ASC
		")->execute();
	}

	public function getEnabledTopFields()
	{
		return $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			WHERE f.parent IS NULL AND f.is_enabled = true
			ORDER BY f.display_order ASC
		")->execute();
	}
}
