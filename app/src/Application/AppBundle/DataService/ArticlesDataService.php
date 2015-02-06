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
 * @subpackage
 */

namespace Application\AppBundle\DataService;


use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ArticlesDataService extends AbstractDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param ArticleCategory $category
     * @param $page
     * @param $max_per_page
     * @return Pagerfanta
     */
    public function getArticlesPager(ArticleCategory $category = null, $page, $max_per_page)
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getArticlesPager',
                $category,
                $page,
                $max_per_page
            ),
            function() use ($em, $category, $max_per_page, $page) {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                    ->from('DeskPRO:Article', 'a')
                    ->where('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
                    ->orderBy('a.id', 'DESC');

                if ($category) {
                    $cat_ids = $category->getTreeIds(true);
                    $qb->leftJoin('a.categories', 'c')
                        ->andWhere('c.id IN (:cat_ids)')->setParameter('cat_ids', $cat_ids);
                }

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Takes null, a category ID, or a ArticleCategory and returns an iterable collection of ArticleCategories
     *
     * Null means ruturn the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|ArticleCategory $category
     * @return ArticleCategory[]
     * @throws \InvalidArgumentException
     */
    public function getCategoryChildren($category)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getCategoryChildren',
                $category
            ),
            function() use ($that, $category) {
                if (!$category) { // get root categories
                    return $that->getArticleCategoriesRepo()->findBy(array('parent' => null));
                }

                if (!$category instanceof ArticleCategory) { // if not already category, try to make it one
                    if (!$category = $that->getCategory($category)) {
                        throw new \InvalidArgumentException(sprintf('could not convert "%s" into an article category'));
                    }
                }

                return $category->children;
            }
        );
    }

    /**
     * @param int|null|Article $article
     * @return Article|null
     */
    public function getArticle($article)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getArticle',
                $article
            ),
            function() use ($that, $article) {
                if (!$article) { // we need some input
                    return null;
                }

                if ($article instanceof Article) { // already have what you seek
                    return $article;
                }

                return $that->getArticlesRepo()->find($article);
            }
        );
    }

    /**
     * Get a category based on arbirtary input
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|ArticleCategory $category
     * @return ArticleCategory|null
     */
    public function getCategory($category)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getCategory',
                $category
            ),
            function () use ($that, $category) {
                if (!$category) { // we need some input
                    return null;
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
                $person
            ),
            function() use ($that, $article, $person) {
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
 