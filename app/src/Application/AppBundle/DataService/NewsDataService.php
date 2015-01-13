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


use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;

class NewsDataService
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
     * @param NewsCategory $category
     * @param $page
     * @param $max_per_page
     * @return Pagerfanta
     */
    public function getNewsPager(NewsCategory $category = null, $page, $max_per_page)
    {
        // TODO: optimize this query
        if ($category) {
            $dls = new ArrayCollection($this->getNewsRepo()->getNews($category, 2000));
        } else {
            $dls = new ArrayCollection($this->getNewsRepo()->getNews(null, 2000));
        }
        // TODO: make sure this collection adapter gets a collection that is EXTRA_LAZY!
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($dls));
        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * Takes null, a category ID, or a NewsCategory and returns an iterable collection of NewsCategories
     *
     * Null means ruturn the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|NewsCategory $category
     * @return NewsCategory[]
     * @throws \InvalidArgumentException
     */
    public function getCategoryChildren($category)
    {
        if (!$category) { // get root categories
            return $this->getNewsCategoriesRepo()->findBy(array('parent' => null));
        }

        if (!$category instanceof NewsCategory) { // if not already category, try to make it one
            if (!$category = $this->getCategory($category)) {
                throw new \InvalidArgumentException(sprintf('could not convert "%s" into a download category'));
            }
        }

        return $category->children;
    }

    /**
     * @param int|null|News $post
     * @return News|null
     */
    public function getPost($post)
    {
        if (!$post) { // we need some input
            return null;
        }

        if ($post instanceof News) { // already have what you seek
            return $post;
        }

        return $this->getNewsRepo()->find($post);
    }

    /**
     * Get a category based on arbirtary input
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|NewsCategory $category
     * @return NewsCategory|null
     */
    public function getCategory($category)
    {
        if (!$category) { // we need some input
            return null;
        }

        if ($category instanceof NewsCategory) { // already have what you seek
            return $category;
        }

        return $this->getNewsCategoriesRepo()->find($category);
    }

    public function getPostComments($post, Person $person = null)
    {
        $post = $this->getPost($post);

        return $this->getNewsCommentRepo()->getDisplayComments($post, $person);
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
 