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
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;

class DownloadsDataService
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
     * @param DownloadCategory $category
     * @param $page
     * @param $max_per_page
     * @return Pagerfanta
     */
    public function getDownloadsPager(DownloadCategory $category = null, $page, $max_per_page)
    {
        // TODO: optimize this query
        if ($category) {
            $dls = new ArrayCollection($this->getDownloadsRepo()->getNewest(2000, $category));
        } else {
            $dls = new ArrayCollection($this->getDownloadsRepo()->getNewest(2000, null));
        }

        // TODO: make sure this collection adapter gets a collection that is EXTRA_LAZY!
        $pager = new Pagerfanta(new DoctrineCollectionAdapter($dls));
        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * Takes null, a category ID, or a DownloadCategory and returns an iterable collection of DownloadCategories
     *
     * Null means ruturn the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|DownloadCategory $category
     * @return DownloadCategory[]
     * @throws \InvalidArgumentException
     */
    public function getCategoryChildren($category)
    {
        if (!$category) { // get root categories
            return $this->getDownloadCategoriesRepo()->findBy(array('parent' => null));
        }

        if (!$category instanceof DownloadCategory) { // if not already category, try to make it one
            if (!$category = $this->getCategory($category)) {
                throw new \InvalidArgumentException(sprintf('could not convert "%s" into a download category'));
            }
        }

        return $category->children;
    }

    /**
     * @param int|null|Download $download
     * @return Download|null
     */
    public function getDownload($download)
    {
        if (!$download) { // we need some input
            return null;
        }

        if ($download instanceof Download) { // already have what you seek
            return $download;
        }

        return $this->getDownloadsRepo()->find($download);
    }

    /**
     * Get a category based on arbirtary input
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|DownloadCategory $category
     * @return DownloadCategory|null
     */
    public function getCategory($category)
    {
        if (!$category) { // we need some input
            return null;
        }

        if ($category instanceof DownloadCategory) { // already have what you seek
            return $category;
        }

        return $this->getDownloadCategoriesRepo()->find($category);
    }

    /**
     * @param int|null|Download $file
     * @return Download[]
     */
    public function getRelatedFiles($file)
    {
        if (!$file = $this->getDownload($file)) {
            return array();
        }

        $related_associations = $this->getRelatedContentRepo()->findRelatedFiles($file);

        $ids = array();
        foreach ($related_associations as $related_association) {
            if ($related_association->rel_object_id) {
                $ids[] = $related_association->rel_object_id;
            }
        }

        return $this->getDownloadsRepo()->findBy(array('id' => $ids), array('date_created' => 'DESC'));
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Download
     */
    public function getDownloadsRepo()
    {
        return $this->em->getRepository('DeskPRO:Download');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\DownloadCategory
     */
    public function getDownloadCategoriesRepo()
    {
        return $this->em->getRepository('DeskPRO:DownloadCategory');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RelatedContent
     */
    public function getRelatedContentRepo()
    {
        return $this->em->getRepository('DeskPRO:RelatedContent');
    }
}
 