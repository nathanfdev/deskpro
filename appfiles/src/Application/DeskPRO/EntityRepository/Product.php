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

class Product extends EntityRepository
{
	protected $_product_names = null;

	protected function _loadProductNames()
	{
		if ($this->_product_names !== null) return;

		if (($this->_product_names = App::getCache('common')->load('product_names')) === false) {
			$db = App::getDb();
			$this->_product_names = $db->fetchAllKeyValue("
				SELECT id, title
				FROM products
				ORDER BY title ASC
			");

			App::getCache('common')->save($this->_product_names, null, array('products'));
		}
	}

	/**
	 * @return array
	 */
	public function getProductNames($for_ids = null)
	{
		$this->_loadProductNames();

		if ($for_ids === null) {
			return $this->_product_names;
		}

		$ret = array();
		foreach ($for_ids as $id) {
			if (isset($this->_product_names[$id])) {
				$ret[] = $this->_product_names[$id];
			}
		}

		return $ret;
	}



	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('products'));
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