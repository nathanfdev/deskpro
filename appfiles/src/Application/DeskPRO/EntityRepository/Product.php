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
use Orb\Util\Arrays;

class Product extends AbstractCategoryRepository
{
	public function findByTitle($title)
	{
		try {
			$product = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:Product p
				WHERE p.title LIKE ?1
			")->setParameter(1, "%$title%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $product;
	}

	public function getProductsById(array $ids)
	{
		$ids = Arrays::removeFalsey($ids);

		if (!$ids) return array();

		$ids = implode(',', $ids);

		return $this->getEntityManager()->createQuery("
			SELECT p
			FROM DeskPRO:Product p
			WHERE p.id IN ($ids)
			ORDER BY p.display_order
		")->execute();
	}


	/**
	 * @return array
	 */
	public function getProductNames($for_ids = null)
	{
		return $this->getCategoryNames($for_ids);
	}

	/**
	 * @return array
	 */
	public function getFullProductNames($sep = ' > ', $include_tops = true)
	{
		return $this->getFullProductNames($sep = ' > ', $include_tops = true);
	}

	/**
	 * @return array
	 */
	public function getProductIds()
	{
		return $this->getCategoryIds();
	}

	/**
	 * @return array
	 */
	public function getProductsInHierarchy()
	{
		return $this->getCategoriesInHierarchy();
	}


	/**
	 * Count all cats that exist
	 *
	 * @return int
	 */
	public function countAll()
	{
		return count($this->getProductNames());
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
