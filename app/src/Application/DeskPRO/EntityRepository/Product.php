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
 * @category Entities
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
		return $this->getCategoryHelper()->getFullCategoryNames($sep = ' > ', $include_tops = true);
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
