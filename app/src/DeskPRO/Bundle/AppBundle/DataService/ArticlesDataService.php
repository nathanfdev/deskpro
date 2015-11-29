<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ArticlesDataService extends AbstractDataService
{
    /**
     * @var PermissionsManager
     */
    protected $permissions_manager;

    public function __construct(EntityManager $em, PermissionsManager $permissions_manager)
    {
        parent::__construct($em);

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
            return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM articles LIMIT 1') ? true : false;
        });
    }

    /**
     * @param ArticleCategory $category
     * @param $page
     * @param $max_per_page
     *
     * @return Pagerfanta
     */
    public function getArticlesPager(ArticleCategory $category = null, $page, $max_per_page, Person $person)
    {
        $em                  = $this->em;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            array(
                'getArticlesPager',
                $category,
                $page,
                $max_per_page,
                $person,
            ),
            function () use ($em, $permissions_manager, $category, $max_per_page, $page, $person) {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                    ->from('DeskPRO:Article', 'a')
                    ->where('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
                    ->orderBy('a.id', 'DESC');

                $allowed_ids = $permissions_manager->getPortalPermissionsBag($person)->getAllowedArticleCategories();
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
                    $qb->leftJoin('a.categories', 'c')
                    ->andWhere('c.id IN (:cat_ids)')->setParameter('cat_ids', $using_ids);

                    $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                }

                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    public function getTopArticlesPager($page, $max_per_page, Person $person)
    {
        $em                  = $this->em;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            array(
                'getTopArticlesPager',
                $page,
                $max_per_page,
                $person,
            ),
            function () use ($em, $permissions_manager, $max_per_page, $page, $person) {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                    ->from('DeskPRO:Article', 'a')
                    ->where('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
                    ->orderBy('a.total_rating', 'DESC');

                $allowed_ids = $permissions_manager->getPortalPermissionsBag($person)->getAllowedArticleCategories();
                $using_ids = $allowed_ids;

                if (empty($using_ids)) {
                    // nocategories are allowed, so no articles are either, returning a blank array pager
                    $pager = new Pagerfanta(new ArrayAdapter(array()));
                } else {
                    $qb->leftJoin('a.categories', 'c')
                        ->andWhere('c.id IN (:cat_ids)')->setParameter('cat_ids', $using_ids);

                    $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                }

                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Takes null, a category ID, or a ArticleCategory and returns an iterable collection of ArticleCategories.
     *
     * Null means ruturn the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|ArticleCategory $category
     *
     * @throws \InvalidArgumentException
     *
     * @return ArticleCategory[]
     */
    public function getCategoryChildren($category, Person $person)
    {
        $that                = $this;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            array(
                'getCategoryChildren',
                $category,
                $person,
            ),
            function () use ($that, $category, $person, $permissions_manager) {
                $allowed_ids = $permissions_manager->getPortalPermissionsBag($person)->getAllowedArticleCategories();

                if (!$category) { // get root categories
                    return $that->getArticleCategoriesRepo()->findBy(array('parent' => null, 'id' => $allowed_ids));
                }

                if (!$category instanceof ArticleCategory) { // if not already category, try to make it one
                    if (!$category = $that->getCategory($category)) {
                        throw new \InvalidArgumentException(sprintf('could not convert "%s" into an article category'));
                    }
                }

                $children = $category->children;

                $result = array();
                foreach ($children as $child) {
                    if (in_array($child->getId(), $allowed_ids)) {
                        $result[] = $child;
                    }
                }

                return $result;
            }
        );
    }

    /**
     * @param int|null|Article $article
     *
     * @return Article|null
     */
    public function getArticle($article)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getArticle',
                $article,
            ),
            function () use ($that, $article) {
                if (!$article) { // we need some input
                    return;
                }

                if ($article instanceof Article) { // already have what you seek
                    return $article;
                }

                return $that->getArticlesRepo()->find($article);
            }
        );
    }

    /**
     * Get a category based on arbirtary input.
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|ArticleCategory $category
     *
     * @return ArticleCategory|null
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

                if ($category instanceof ArticleCategory) { // already have what you seek
                    return $category;
                }

                return $that->getArticleCategoriesRepo()->find($category);
            }
        );
    }

    public function getArticleComments($article, Person $person = null)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getArticleComments',
                $article,
                $person,
            ),
            function () use ($that, $article, $person) {
                $article = $that->getArticle($article);

                return $that->getArticleCommentRepo()->getDisplayComments($article, $person);
            }
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Article
     */
    public function getArticlesRepo()
    {
        return $this->em->getRepository('DeskPRO:Article');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\ArticleCategory
     */
    public function getArticleCategoriesRepo()
    {
        return $this->em->getRepository('DeskPRO:ArticleCategory');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\ArticleComment
     */
    public function getArticleCommentRepo()
    {
        return $this->em->getRepository('DeskPRO:ArticleComment');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RelatedContent
     */
    public function getRelatedContentRepo()
    {
        return $this->em->getRepository('DeskPRO:RelatedContent');
    }
}
