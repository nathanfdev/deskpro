<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RelatedContent;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ArticlesDataService extends AbstractDataService
{
    /**
     * @var PermissionsManager
     */
    protected $permissionsManager;

    public function __construct(EntityManager $em, PermissionsManager $permissionsManager)
    {
        parent::__construct($em);

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
            return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM articles LIMIT 1') ? true : false;
        });
    }

    /**
     * @param ArticleCategory $category
     * @param                 $page
     * @param                 $maxPerPage
     * @param Person          $person
     * @param bool            $withTree
     *
     * @return Pagerfanta
     */
    public function getArticlesPager(ArticleCategory $category = null, $page, $maxPerPage, Person $person, $withTree = false)
    {
        $em                 = $this->em;
        $permissionsManager = $this->permissionsManager;

        return $this->generateAndCache(
            [
                'getArticlesPager',
                $category,
                (int) $page,
                (int) $maxPerPage,
                $person,
                (bool) $withTree,
            ],
            function () use ($em, $permissionsManager, $category, $maxPerPage, $page, $person, $withTree) {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                    ->from(Article::class, 'a')
                    ->where('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
                    ->orderBy('a.id', 'DESC');

                $allowedIds = $permissionsManager->getPortalPermissionsBag($person)->getAllowedArticleCategories();
                if ($category) {
                    if ($withTree) {
                        // find allowed ids
                        $catIds = $category->getTreeIds(true);
                        $usingIds = [];
                        foreach ($catIds as $catId) {
                            if (in_array($catId, $allowedIds)) {
                                $usingIds[] = $catId;
                            }
                        }
                    } else {
                        $usingIds = [$category->getId()];
                    }
                } else {
                    $usingIds = $allowedIds;
                }

                if (empty($usingIds)) {
                    // no categories are allowed, so no articles are either, returning a blank array pager
                    $pager = new Pagerfanta(new ArrayAdapter([]));
                } else {
                    $qb->leftJoin('a.categories', 'c')
                        ->andWhere('c.id IN (:cat_ids)')->setParameter('cat_ids', $usingIds);

                    $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                }

                $pager->setMaxPerPage($maxPerPage);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    public function getTopArticlesPager($page, $maxPerPage, Person $person)
    {
        $em                 = $this->em;
        $permissionsManager = $this->permissionsManager;

        return $this->generateAndCache(
            [
                'getTopArticlesPager',
                (int) $page,
                (int) $maxPerPage,
                $person,
            ],
            function () use ($em, $permissionsManager, $maxPerPage, $page, $person) {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                    ->from(Article::class, 'a')
                    ->where('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
                    ->orderBy('a.total_rating', 'DESC');

                $allowedIds = $permissionsManager->getPortalPermissionsBag($person)->getAllowedArticleCategories();
                $usingIds = $allowedIds;

                if (empty($usingIds)) {
                    // nocategories are allowed, so no articles are either, returning a blank array pager
                    $pager = new Pagerfanta(new ArrayAdapter([]));
                } else {
                    $qb->leftJoin('a.categories', 'c')
                        ->andWhere('c.id IN (:cat_ids)')->setParameter('cat_ids', $usingIds);

                    $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                }

                $pager->setMaxPerPage($maxPerPage);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Takes null, a category ID, or a ArticleCategory and returns an iterable collection of ArticleCategories.
     *
     * Null means return the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|ArticleCategory $category
     * @param Person                   $person
     *
     * @return \Application\DeskPRO\Entity\ArticleCategory[]
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
                )->getAllowedArticleCategories();

                if (!$category) { // get root categories
                    $result = $that->getArticleCategoriesRepo()
                        ->findBy([
                            'parent' => null,
                            'id'     => $allowedIds,
                        ]);
                } else {
                    if (!$category instanceof ArticleCategory) { // if not already category, try to make it one
                        if (!$category = $that->getCategory($category)) {
                            throw new \InvalidArgumentException(sprintf('could not convert "%s" into an article category'));
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
                    /* @var ArticleCategory $v */
                    return $v->getDisplayOrder();
                });

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
            [
                'getArticle',
                $article,
            ],
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
            [
                'getCategory',
                $category,
            ],
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
            [
                'getArticleComments',
                $article,
                $person,
            ],
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
        return $this->em->getRepository(Article::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\ArticleCategory
     */
    public function getArticleCategoriesRepo()
    {
        return $this->em->getRepository(ArticleCategory::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\ArticleComment
     */
    public function getArticleCommentRepo()
    {
        return $this->em->getRepository(ArticleComment::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RelatedContent
     */
    public function getRelatedContentRepo()
    {
        return $this->em->getRepository(RelatedContent::class);
    }
}
