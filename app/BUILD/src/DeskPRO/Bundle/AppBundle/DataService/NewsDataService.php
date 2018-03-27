<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RelatedContent;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class NewsDataService extends AbstractDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var PermissionsManager
     */
    protected $permissionsManager;

    public function __construct(EntityManager $em, PermissionsManager $permissionsManager)
    {
        $this->em                 = $em;
        $this->permissionsManager = $permissionsManager;
    }

    /**
     * @return bool
     */
    public function hasAny()
    {
        $em = $this->em;

        return $this->generateAndCache(['hasAny'], function () use ($em) {
            return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM news LIMIT 1') ? true : false;
        });
    }

    /**
     * @param NewsCategory $category
     * @param              $page
     * @param              $max_per_page
     * @param Person       $person
     *
     * @return Pagerfanta
     */
    public function getNewsPager(NewsCategory $category = null, $page, $max_per_page, Person $person)
    {
        $em                 = $this->em;
        $permissionsManager = $this->permissionsManager;

        return $this->generateAndCache(
            [
                'getNewsPager',
                $category,
                (int) $page,
                (int) $max_per_page,
                $person,
            ],
            function () use ($em, $permissionsManager, $category, $max_per_page, $page, $person) {
                $qb = $em->createQueryBuilder();

                $qb->select('n')
                    ->from(News::class, 'n')
                    ->where('n.status = :status')->setParameter('status', News::STATUS_PUBLISHED)
                    ->orderBy('n.date_published', 'DESC');

                $allowed_ids = $permissionsManager->getPortalPermissionsBag($person)->getAllowedNewsCategories();
                if ($category) {
                    // find allowed ids
                    $cat_ids = $category->getTreeIds(true);
                    $using_ids = [];
                    foreach ($cat_ids as $cat_id) {
                        if (in_array($cat_id, $allowed_ids)) {
                            $using_ids[] = $cat_id;
                        }
                    }
                } else {
                    $using_ids = $allowed_ids;
                }

                if (empty($using_ids)) {
                    // no categories are allowed, so no articles are either, returning a blank array pager
                    $pager = new Pagerfanta(new ArrayAdapter([]));
                } else {
                    $qb->leftJoin('n.category', 'c')
                        ->andWhere('c IN (:cat)')->setParameter('cat', $using_ids);

                    $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                }

                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Takes null, a category ID, or a NewsCategory and returns an iterable collection of NewsCategories.
     *
     * Null means ruturn the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|NewsCategory $category
     * @param Person                $person
     *
     * @return \Application\DeskPRO\Entity\NewsCategory[]
     */
    public function getCategoryChildren($category, Person $person)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getCategoryChildren',
                $category,
                $person,
            ],
            function () use ($that, $category, $person) {
                $allowedIds = $that->permissionsManager->getPortalPermissionsBag(
                    $person
                )->getAllowedNewsCategories();

                if (!$category) { // get root categories
                    $result = $that->getNewsCategoriesRepo()
                        ->findBy([
                            'parent' => null,
                            'id'     => $allowedIds,
                        ]);
                } else {
                    if (!$category instanceof NewsCategory) { // if not already category, try to make it one
                        if (!$category = $that->getCategory($category)) {
                            throw new \InvalidArgumentException(sprintf('could not convert "%s" into a download category'));
                        }
                    }

                    $children = $category->getChildren();

                    $result = [];
                    foreach ($children as $child) {
                        if (in_array($child->getId(), $allowedIds)) {
                            $result[] = $child;
                        }
                    }
                }

                $result = ListUtils::sortByFnValue($result, function ($v) {
                    /* @var NewsCategory $v */
                    return $v->getDisplayOrder();
                });

                return $result;
            }
        );
    }

    /**
     * @param int|null|News $post
     *
     * @return News|null
     */
    public function getPost($post)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getPost',
                $post,
            ],
            function () use ($that, $post) {
                if (!$post) { // we need some input
                    return;
                }

                if ($post instanceof News) { // already have what you seek
                    return $post;
                }

                return $that->getNewsRepo()->find($post);
            }
        );
    }

    /**
     * Get a category based on arbirtary input.
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|NewsCategory $category
     *
     * @return NewsCategory|null
     */
    public function getCategory($category)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getCategory',
                $category,
            ],
            function () use ($that, $category) {
                if (!$category) { // we need some input
                    return;
                }

                if ($category instanceof NewsCategory) { // already have what you seek
                    return $category;
                }

                return $that->getNewsCategoriesRepo()->find($category);
            }
        );
    }

    public function getPostComments($post, Person $person = null)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getPostComments',
                $post,
                $person,
            ],
            function () use ($that, $post, $person) {
                $post = $that->getPost($post);

                return $that->getNewsCommentRepo()->getDisplayComments($post, $person);
            }
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\News
     */
    public function getNewsRepo()
    {
        return $this->em->getRepository(News::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\NewsCategory
     */
    public function getNewsCategoriesRepo()
    {
        return $this->em->getRepository(NewsCategory::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\NewsComment
     */
    public function getNewsCommentRepo()
    {
        return $this->em->getRepository(NewsComment::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RelatedContent
     */
    public function getRelatedContentRepo()
    {
        return $this->em->getRepository(RelatedContent::class);
    }
}
