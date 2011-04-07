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

class IdeaCategory extends EntityRepository
{
	protected $_cat_names = null;

	protected function _loadNames()
	{
		if ($this->_cat_names !== null) return;

		if (($this->_cat_names = App::getCache('common')->load('idea_category_names')) === false) {
			$db = App::getDb();
			$this->_cat_names = $db->fetchAllKeyValue("
				SELECT id, title
				FROM idea_categories
				ORDER BY title ASC
			");

			App::getCache('common')->save($this->_cat_names, null, array('idea_category_names'));
		}
	}

	/**
	 * @return array
	 */
	public function getCategoryNames($for_ids = null)
	{
		$this->_loadNames();

		if ($for_ids === null) {
			return $this->_cat_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_cat_names[$id])) {
				$ret[] = $this->_cat_names[$id];
			}
		}

		return $ret;
	}



	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('idea_category_names'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}