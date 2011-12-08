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

class CustomDefAbstract extends AbstractEntityRepository
{
	public static function getCacheId($id)
	{
		return 'customdef' . get_called_class() . '_' . $id;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		$q = $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			ORDER BY f.display_order ASC
		");
		$q->useResultCache(true, null, static::getCacheId('getfields'));

		return $q->execute();
	}

	public function getEnabledFields()
	{
		$q = $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			WHERE f.is_enabled = true
			ORDER BY f.display_order ASC
		");
		$q->useResultCache(true, null, static::getCacheId('getenabledfields'));

		return $q->execute();
	}

	/**
	 * @return array
	 */
	public function getTopFields()
	{
		$q = $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			WHERE f.parent IS NULL
			ORDER BY f.display_order ASC
		");

		$q->useResultCache(true, null, static::getCacheId('gettopfields'));

		return $q->execute();
	}

	public function getEnabledTopFields()
	{
		$q = $this->_em->createQuery("
			SELECT f
			FROM {$this->_entityName} f
			WHERE f.parent IS NULL AND f.is_enabled = true
			ORDER BY f.display_order ASC
		");

		$q->useResultCache(true, null, static::getCacheId('gettopfields'));

		return $q->execute();
	}
}
