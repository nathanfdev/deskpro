<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\EntityRepository\AbstractCategoryRepository;
use Orb\Util\Arrays;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Helps fetch info related to structure of Publish.
 */
class CategoryEdit
{
    const ARTICLES = 'articles';

    const DOWNLOADS = 'downloads';

    const NEWS = 'news';

    /**
     * Add a new category to the systme.
     *
     *
     * @param $type
     * @param $title
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Entity\ArticleCategory|\Application\DeskPRO\Entity\DownloadCategory|\Application\DeskPRO\Entity\NewsCategory|array
     */
    public static function addCategory($type, $title)
    {
        switch ($type) {
            case self::ARTICLES:
                $obj = new ArticleCategory();
                break;
            case self::DOWNLOADS:
                $obj = new DownloadCategory();
                break;
            case self::NEWS:
                $obj = new NewsCategory();
                break;
            default:
                throw new \InvalidArgumentException("Unknown type `$type`");
        }

        $obj['title'] = $title;

        App::getOrm()->persist($obj);

        /** @var AbstractCategoryRepository $categoryRepository */
        $categoryRepository = App::getOrm()->getRepository(get_class($obj));
        $categoryRepository->repair();
        App::getOrm()->flush();

        // By default also add 'Everyone' permission
        $permissionsTable = $categoryRepository->getPermissionTableName();
        App::getDb()->insert($permissionsTable, [
            'category_id'  => $obj->getId(),
            'usergroup_id' => '1',
        ]);

        App::getContainer()->getSystemService('publish_structure_cache')->flush();

        return $obj;
    }

    /**
     * Update titles for categories. $titles is id=>title.
     *
     * @param string $type
     * @param array  $titles
     *
     * @return array
     */
    public static function updateTitles($type, array $titles)
    {
        $entity = self::getEntityNameFor($type);

        $ids = array_keys($titles);
        $ids = Arrays::castToType($ids, 'integer');

        if (!$ids) {
            return [];
        }

        $cats = App::getOrm()->createQuery("
            SELECT c
            FROM $entity c INDEX BY c.id
            WHERE c.id IN (".implode(',', $ids).')
        ')->execute();

        App::getOrm()->beginTransaction();

        foreach ($titles as $id => $title) {
            if (!isset($cats[$id])) {
                continue;
            }

            $cats[$id]['title'] = $title;
            App::getOrm()->persist($cats[$id]);
        }

        App::getOrm()->flush();

        App::getContainer()->getSystemService('publish_structure_cache')->flush();
        App::getOrm()->commit();

        return $cats;
    }

    /**
     * @param       $type
     * @param       $categoryId
     * @param       $title
     * @param array $usergroupIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public static function update($type, $categoryId, $title, array $usergroupIds)
    {
        $entity = self::getEntityNameFor($type);
        /** @var AbstractCategoryRepository $categoryRepository */
        $categoryRepository = App::getEntityRepository($entity);
        $permissionsTable   = $categoryRepository->getPermissionTableName();
        $category           = App::getOrm()->find($entity, $categoryId);

        if (!$category) {
            throw new NotFoundHttpException();
        }

        $usergroupIds = Arrays::castToType($usergroupIds, 'integer');
        $usergroupIds = Arrays::removeFalsey($usergroupIds);
        $usergroupIds = array_unique($usergroupIds);

        App::getOrm()->beginTransaction();

        try {
            $category->title = $title;

            if ($permissionsTable) {
                App::getDb()->delete($permissionsTable, ['category_id' => $category->id]);

                foreach ($usergroupIds as $uid) {
                    App::getDb()->insert($permissionsTable, [
                        'category_id'  => $category->id,
                        'usergroup_id' => $uid,
                    ]);
                }
            }

            App::getOrm()->persist($category);
            App::getOrm()->flush();
            App::getOrm()->commit();

            App::getContainer()->getSystemService('publish_structure_cache')->flush();
            App::getDb()->query('DELETE FROM permissions_cache');
        } catch (\Exception $e) {
            App::getOrm()->rollback();
            throw $e;
        }
    }

    /**
     * Update orders. $orders is an array of ID's in the order you want them.
     *
     * @param string $type
     * @param array  $orders
     *
     * @throws \Exception
     */
    public static function updateOrders($type, array $orders)
    {
        $entity = self::getEntityNameFor($type);

        $ids = array_values($orders);
        $ids = array_unique($ids);
        $ids = Arrays::castToType($ids, 'integer');

        if (!$ids) {
            return;
        }

        $cats = App::getOrm()->createQuery("
            SELECT c
            FROM $entity c INDEX BY c.id
            WHERE c.id IN (".implode(',', $ids).')
        ')->execute();

        App::getDb()->beginTransaction();

        try {
            foreach ($ids as $order => $id) {
                if (!isset($cats[$id])) {
                    continue;
                }

                $cats[$id]['display_order'] = ($order + 1) * 10; // 10,20,30, etc
                App::getOrm()->persist($cats[$id]);
            }

            App::getOrm()->flush();

            /** @var AbstractCategoryRepository $categoryRepository */
            $categoryRepository = App::getOrm()->getRepository($entity);
            $categoryRepository->repair();

            App::getContainer()->getSystemService('publish_structure_cache')->flush();
            App::getOrm()->flush();
            App::getDb()->commit();
        } catch (\Exception $e) {
            App::getDb()->rollback();
            throw $e;
        }
    }

    /**
     * Update the structure based off a map of ids to categories.
     *
     * @param string $type
     * @param array  $map
     *
     * @return array
     */
    public static function updateStructure($type, array $map, array $checkMap = null)
    {
        $entity = self::getEntityNameFor($type);

        $entityManager = App::getOrm();
        $cats          = $entityManager->createQuery("
            SELECT c
            FROM $entity c INDEX BY c.id
        ")->execute();

        /** @var AbstractCategoryRepository $categoryRepository */
        $categoryRepository = $entityManager->getRepository($entity);
        // If theres a check map then we want to verify that the current tree is the same,
        // or else error out
        if ($checkMap) {
            $connection  = App::getDb();
            $table       = $categoryRepository->getTableName();
            $currentTree = $connection->fetchAllKeyValue(
                'SELECT id, parent_id FROM '.$connection->quoteIdentifier($table)
            );

            $accurate = true;
            foreach ($checkMap as $id => $parentId) {
                if (array_key_exists($parentId, $currentTree)) {
                    $currentParentId = isset($currentTree[$id]) ? $currentTree[$id] : null;
                    if ($currentParentId === null) {
                        $currentParentId = 0;
                    }

                    if ($currentParentId != $parentId) {
                        $accurate = false;
                        break;
                    }
                }
            }

            if (!$accurate) {
                throw new \OutOfBoundsException('Structure check failed');
            }
        }

        $entityManager->beginTransaction();

        foreach ($map as $id => $parentId) {
            if (!isset($cats[$id])) {
                continue;
            }
            if (!$parentId) {
                $cats[$id]['parent'] = null;
            } else {
                if (!isset($cats[$parentId])) {
                    continue;
                }
                $cats[$id]['parent'] = $cats[$parentId];
            }
            $entityManager->persist($cats[$id]);
        }

        $categoryRepository->repair();
        $entityManager->flush();
        App::getContainer()->getSystemService('publish_structure_cache')->flush();
        $entityManager->commit();

        return $cats;
    }

    /**
     * Deletes a category and all its children if they are empty.
     *
     * @param string $type
     * @param int    $categoryId
     *
     * @throws \InvalidArgumentException
     *
     * @return CategoryAbstract
     */
    public static function deleteCategory($type, $categoryId)
    {
        $entity             = self::getEntityNameFor($type);
        $categoryRepository = App::getOrm()->getRepository($entity);
        $category           = $categoryRepository->find($categoryId);

        if (!$category) {
            throw new NotFoundHttpException();
        }

        switch ($type) {
            case 'articles':
                $counts = App::getDb()->fetchColumn("
                    SELECT COUNT(*)
                    FROM article_to_categories
                    LEFT JOIN articles ON articles.id = article_to_categories.article_id
                    WHERE category_id = ? 
                    AND (
                      articles.hidden_status IS NULL OR 
                      (
                          articles.hidden_status != 'deleted' 
                      AND articles.hidden_status != 'draft'
                      AND articles.hidden_status != 'spam'
                      )
                    )
                    LIMIT 1
                ", [$categoryId]);
                break;
            case 'downloads':
                $counts = App::getDb()->fetchColumn("
                    SELECT COUNT(*)
                    FROM downloads
                    WHERE category_id = ? AND (hidden_status IS NULL OR hidden_status != 'deleted')
                    LIMIT 1
                ", [$categoryId]);
                break;
            case 'news':
                $counts = App::getDb()->fetchColumn("
                    SELECT COUNT(*)
                    FROM news
                    WHERE category_id = ? AND (hidden_status IS NULL OR hidden_status != 'deleted')
                    LIMIT 1
                ", [$categoryId]);
                break;
            case 'feedback':
                $counts = App::getDb()->fetchColumn("
                    SELECT COUNT(*)
                    FROM feedback
                    WHERE category_id = ? AND (hidden_status IS NULL OR hidden_status != 'deleted')
                    LIMIT 1
                ", [$categoryId]);
                break;
            case 'topics':
                $counts = App::getDb()->fetchColumn("
                    SELECT COUNT(*)
                    FROM topics
                    WHERE guide_id = ? AND (hidden_status IS NULL OR hidden_status != 'deleted')
                    LIMIT 1
                ", [$categoryId]);
                break;
            default:
                $counts = 0;
                break;
        }

        if (count($category->children) || $counts) {
            throw new \OutOfBoundsException('Category is not empty');
        }

        App::getOrm()->beginTransaction();

        $fn = function ($delcat) use (&$fn) {
            if ($delcat->children) {
                foreach ($delcat->children as $subcat) {
                    $fn($subcat);
                }
            }

            App::getOrm()->remove($delcat);
        };

        $fn($category);

        App::getOrm()->flush();

        $categoryRepository->repair();
        App::getContainer()->getSystemService('publish_structure_cache')->flush();
        App::getOrm()->commit();

        return $category;
    }

    /**
     * Get the content entity for a publish type.
     *
     * @static
     *
     * @param $type
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function getEntityNameFor($type)
    {
        return AgentHelper::getCatEntityNameFor($type);
    }
}
