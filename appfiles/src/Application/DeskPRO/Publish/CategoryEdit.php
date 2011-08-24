<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Addons
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\People\PersonContextInterface;

use Application\DeskPRO\Searcher\ArticleSearch;

use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Helps fetch info related to structure of Publish
 */
class CategoryEdit
{
	const ARTICLES  = 'articles';
	const DOWNLOADS = 'downloads';
	const NEWS      = 'news';

	/**
	 * Add a new category to the systme
	 *
	 * @throws \InvalidArgumentException
	 * @param $type
	 * @param $title
	 * @return \Application\DeskPRO\Entity\ArticleCategory|\Application\DeskPRO\Entity\DownloadCategory|\Application\DeskPRO\Entity\NewsCategory|array
	 */
	public static function addCategory($type, $title)
	{
		switch ($type) {
			case self::ARTICLES:
				$obj = new ArticleCategory;
				break;
			case self::DOWNLOADS:
				$obj = new DownloadCategory;
				break;
			case self::NEWS:
				$obj = new NewsCategory;
				break;
			default:
				throw new \InvalidArgumentException("Unknow type `$type`");
		}

		$obj['title'] = $title;

		App::getOrm()->persist($obj);
		App::getOrm()->flush();

		return $obj;
	}


	/**
	 * Update titles for categoryes. $titles is id=>title
	 *
	 * @param $type
	 * @param array $titles
	 * @return array
	 */
	public static function updateTitles($type, array $titles)
	{
		$entity = self::getEntityNameFor($type);

		$ids = array_keys($titles);
		$ids = Arrays::castToType($ids, 'integer');

		if (!$ids) {
			return array();
		}

		$cats = App::getOrm()->createQuery("
			SELECT c
			FROM $entity c INDEX BY c.id
			WHERE c.id IN (" . implode(',', $ids) . ")
		")->execute();

		App::getOrm()->beginTransaction();

		foreach ($titles as $id => $title) {
			if (!isset($cats[$id])) {
				continue;
			}

			$cats[$id]['title'] = $title;
			App::getOrm()->persist($cats[$id]);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $cats;
	}


	/**
	 * Update orders. $orders is an array of ID's in the order you want them.
	 *
	 * @param $type
	 * @param array $orders
	 * @return void
	 */
	public static function updateOrders($type, array $orders)
	{
		$entity = self::getEntityNameFor($type);

		$ids = array_values($orders);
		$ids = Arrays::castToType($ids, 'integer');

		$cats = App::getOrm()->createQuery("
			SELECT c
			FROM $entity c INDEX BY c.id
			WHERE c.id IN (" . implode(',', $ids) . ")
		")->execute();

		App::getOrm()->beginTransaction();

		foreach ($ids as $order => $id) {
			if (!isset($cats[$id])) {
				continue;
			}

			$cats[$id]['display_order'] = ($order+1) * 10; // 10,20,30, etc
			App::getOrm()->persist($cats[$id]);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		App::getOrm()->beginTransaction();

		// Update the left/right orders
		App::getEntityRepository($entity)->reorderAll('display_order');

		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	/**
	 * Update the structure based off a map of ids to categories
	 *
	 * @param $type
	 * @param array $map
	 * @return array
	 */
	public static function updateStructure($type, array $map)
	{
		$entity = self::getEntityNameFor($type);

		$cats = App::getOrm()->createQuery("
			SELECT c
			FROM $entity c INDEX BY c.id
		")->execute();

		App::getOrm()->beginTransaction();

		foreach ($map as $id => $parent_id) {
			if (!isset($cats[$id])) {
				continue;
			}

			if (!$parent_id) {
				$cats[$id]['parent'] = null;
			} else {
				if (!isset($cats[$parent_id])) {
					continue;
				}
				$cats[$id]['parent'] = $cats[$parent_id];
			}

			App::getOrm()->persist($cats[$id]);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $cats;
	}


	/**
	 * Deletes a category and all its children if they are empty.
	 *
	 * @throws \InvalidArgumentException
	 * @param $type
	 * @param $category_id
	 * @return void
	 */
	public static function deleteCategory($type, $category_id)
	{
		$entity = self::getEntityNameFor($type);
		$repos  = App::getOrm()->getRepository($entity);
		$cat = $repos->find($category_id);

		if (!$cat) {
			throw new \InvalidArgumentException("Unknown category `$category_id`");
		}

		$counts = $repos->getAllCounts(App::getCurrentPerson(), null);
		if (isset($counts[$cat['id']]) && $counts[$cat['id']]) {
			throw new \OutOfBoundsException("Category is not empty");
		}

		App::getOrm()->beginTransaction();

		$fn = function($delcat) use (&$fn) {
			foreach ($delcat->children as $subcat) {
				$fn($subcat);
			}

			App::getOrm()->remove($delcat);
		};

		$fn($cat);

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $cat;
	}


	/**
	 * Get the content entity for a publish type
	 *
	 * @static
	 * @throws \InvalidArgumentException
	 * @param $type
	 * @return string
	 */
	public static function getEntityNameFor($type)
	{
		return AgentHelper::getCatEntityNameFor($type);
	}
}
