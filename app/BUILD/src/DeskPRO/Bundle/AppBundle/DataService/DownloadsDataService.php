<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RelatedContent;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class DownloadsDataService.
 */
class DownloadsDataService extends AbstractDataService
{
    /**
     * @var PermissionsManager
     */
    protected $permissions_manager;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param PermissionsManager $permissionsManager
     */
    public function __construct(EntityManager $em, PermissionsManager $permissionsManager)
    {
        parent::__construct($em);
        $this->permissions_manager = $permissionsManager;
    }

    /**
     * @return bool
     */
    public function hasAny()
    {
        $em = $this->em;

        return $this->generateAndCache(['hasAny'], function () use ($em) {
            return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM downloads LIMIT 1') ? true : false;
        });
    }

    /**
     * @param DownloadCategory $category
     * @param int              $page
     * @param int              $max_per_page
     * @param Person           $person
     *
     * @return Pagerfanta
     */
    public function getDownloadsPager(DownloadCategory $category = null, $page, $max_per_page, Person $person)
    {
        $em                  = $this->em;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            [
                'getDownloadsPager',
                $category,
                (int) $page,
                (int) $max_per_page,
                $person,
            ],
            function () use ($em, $permissions_manager, $category, $max_per_page, $page, $person) {
                $qb = $em->createQueryBuilder();
                $qb->select('d')
                    ->from(Download::class, 'd')
                    ->where('d.status = :status')->setParameter('status', Download::STATUS_PUBLISHED)
                    ->orderBy('d.id', 'DESC')
                ;

                $allowed_ids = $permissions_manager->getPortalPermissionsBag($person)->getAllowedDownloadCategories();
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
                    // nocategories are allowed, so no articles are either, returning a blank array pager
                    $pager = new Pagerfanta(new ArrayAdapter([]));
                } else {
                    $qb
                        ->leftJoin('d.category', 'c')
                        ->andWhere('c.id IN (:cat)')->setParameter('cat', $using_ids)
                    ;

                    $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                }

                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * Takes null, a category ID, or a DownloadCategory and returns an iterable collection of DownloadCategories.
     *
     * Null means return the roots.
     *
     * TODO: this is using the doctrine proxy as a method of finding children of the category. Might be able to improve that.
     *
     * @param int|null|DownloadCategory $category
     * @param Person                    $person
     *
     * @throws \InvalidArgumentException
     *
     * @return DownloadCategory[]
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
                $allowed_ids = $that->permissions_manager->getPortalPermissionsBag(
                    $person
                )->getAllowedDownloadCategories();

                if (!$category) { // get root categories
                    $result = $that->getDownloadCategoriesRepo()
                        ->findBy([
                            'parent' => null,
                            'id'     => $allowed_ids,
                        ]);
                } else {
                    if (!$category instanceof DownloadCategory) { // if not already category, try to make it one
                        if (!$category = $that->getCategory($category)) {
                            throw new \InvalidArgumentException(sprintf('could not convert "%s" into a download category'));
                        }
                    }
                    $children = $category->getChildren();

                    $result = [];
                    foreach ($children as $child) {
                        if (in_array($child->getId(), $allowed_ids)) {
                            $result[] = $child;
                        }
                    }
                }

                $result = ListUtils::sortByFnValue($result, function ($v) {
                    /* @var DownloadCategory $v */
                    return $v->getDisplayOrder();
                });

                return $result;
            }
        );
    }

    /**
     * @param int|null|Download $download
     *
     * @return Download|null
     */
    public function getDownload($download)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getDownload',
                $download,
            ],
            function () use ($that, $download) {
                if (!$download) { // we need some input
                    return;
                }

                if ($download instanceof Download) { // already have what you seek
                    return $download;
                }

                return $that->getDownloadsRepo()->find($download);
            }
        );
    }

    /**
     * Get a category based on arbirtary input.
     *
     * TODO: optimize the heck out of any possible inputs here (if it helps: cache in an array at least, cache long term if desired, should normalize cache key on lowest common denominator "id")
     *
     * @param int|null|DownloadCategory $category
     *
     * @return DownloadCategory|null
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

                if ($category instanceof DownloadCategory) { // already have what you seek
                    return $category;
                }

                return $that->getDownloadCategoriesRepo()->find($category);
            }
        );
    }

    /**
     * @param mixed       $file
     * @param Person|null $person
     *
     * @return mixed
     */
    public function getDownloadComments($file, Person $person = null)
    {
        $that = $this;

        return $this->generateAndCache(
            [
                'getDownloadComments',
                $file,
                $person,
            ],
            function () use ($that, $file, $person) {
                $file = $that->getDownload($file);

                return $that->getDownloadCommentRepo()->getDisplayComments($file, $person);
            }
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Download
     */
    public function getDownloadsRepo()
    {
        return $this->em->getRepository(Download::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\DownloadCategory
     */
    public function getDownloadCategoriesRepo()
    {
        return $this->em->getRepository(DownloadCategory::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\DownloadComment
     */
    public function getDownloadCommentRepo()
    {
        return $this->em->getRepository(DownloadComment::class);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\RelatedContent
     */
    public function getRelatedContentRepo()
    {
        return $this->em->getRepository(RelatedContent::class);
    }
}
