<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
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
    protected $permissions_manager;

    public function __construct(EntityManager $em, PermissionsManager $permissions_manager)
    {
        $this->em                  = $em;
        $this->permissions_manager = $permissions_manager;
    }

    /**
     * @return bool
     */
    public function hasAny()
    {
        $em = $this->em;

        return $this->generateAndCache(array('hasAny'), function () use ($em) {
            return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM news LIMIT 1') ? true : false;
        });
    }

    /**
     * @param NewsCategory $category
     * @param $page
     * @param $max_per_page
     *
     * @return Pagerfanta
     */
    public function getNewsPager(NewsCategory $category = null, $page, $max_per_page, Person $person)
    {
        $em                  = $this->em;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            array(
                'getNewsPager',
                $category,
                $page,
                $max_per_page,
                $person,
            ),
            function () use ($em, $permissions_manager, $category, $max_per_page, $page, $person) {
                $qb = $em->createQueryBuilder();

                $qb->select('n')
                    ->from('DeskPRO:News', 'n')
                    ->where('n.status = :status')->setParameter('status', News::STATUS_PUBLISHED)
                    ->orderBy('n.id', 'DESC');

                $allowed_ids = $permissions_manager->getPortalPermissionsBag($person)->getAllowedNewsCategories();
                if ($category) {
                    // find allowed ids
                    $cat_ids = $category->getTreeIds(true);
                    $using_ids = array();
                    foreach ($cat_ids as $cat_id) {
                        if (in_array($cat_id, $allowed_ids)) {
                            $using_ids[] = $cat_id;
                        }
                    }
                } else {
                    $using_ids = $allowed_ids;
                }

                if (empty($using_ids)) {
                    // nocategories are allowed, so no articles are either, returning a blank array pager
                    $pager = new Pagerfanta(new ArrayAdapter(array()));
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
     *
     * @throws \InvalidArgumentException
     *
     * @return NewsCategory[]
     */
    public function getCategoryChildren($category, Person $person)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getCategoryChildren',
                $category,
                $person,
            ),
            function () use ($that, $category, $person) {
                $allowed_ids = $that->permissions_manager->getPortalPermissionsBag(
                    $person
                )->getAllowedNewsCategories();

                if (!$category) { // get root categories
                    $result = $that->getNewsCategoriesRepo()->findBy(array('parent' => null, 'id' => $allowed_ids));
                } else {
                    if (!$category instanceof NewsCategory) { // if not already category, try to make it one
                        if (!$category = $that->getCategory($category)) {
                            throw new \InvalidArgumentException(sprintf('could not convert "%s" into a download category'));
                        }
                    }

                    $children = $category->children;

                    $result = array();
                    foreach ($children as $child) {
                        if (in_array($child->getId(), $allowed_ids)) {
                            $result[] = $child;
                        }
                    }
                }

                $result = ListUtils::sortByFnValue($result, function ($v) { return $v->getDisplayOrder(); });

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
            array(
                'getPost',
                $post,
            ),
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
            array(
                'getCategory',
                $category,
            ),
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
            array(
                'getPostComments',
                $post,
                $person,
            ),
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
        return $this->em->getRepository('DeskPRO:News');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\NewsCategory
     */
    public function getNewsCategoriesRepo()
    {
        return $this->em->getRepository('DeskPRO:NewsCategory');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\NewsComment
     */
    public function getNewsCommentRepo()
    {
        return $this->em->getRepository('DeskPRO:NewsComment');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RelatedContent
     */
    public function getRelatedContentRepo()
    {
        return $this->em->getRepository('DeskPRO:RelatedContent');
    }
}
