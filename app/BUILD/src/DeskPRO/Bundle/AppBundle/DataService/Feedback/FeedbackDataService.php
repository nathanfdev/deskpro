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

namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\PortalBundle\Model\FeedbackFilter;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackDataService extends AbstractDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var PermissionsManager
     */
    protected $permissions_manager;

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

        return $this->generateAndCache(
            array('hasAny'),
            function () use ($em) {
                return $em->getConnection()->fetchColumn('SELECT COUNT(*) FROM feedback LIMIT 1') ? true : false;
            }
        );
    }

    /**
     * @param                $page
     * @param                $max_per_page
     * @param FeedbackFilter $filter
     * @param Person         $person
     *
     * @return Pagerfanta
     */
    public function getItemsPager($page, $max_per_page, FeedbackFilter $filter, Person $person)
    {
        $em                  = $this->em;
        $permissions_manager = $this->permissions_manager;

        return $this->generateAndCache(
            array(
                'getItemsPager',
                $page,
                $max_per_page,
                $filter,
                $person,
            ),
            function () use ($em, $permissions_manager, $page, $max_per_page, $filter, $person) {
                $qb = $em->createQueryBuilder();
                $qb->select('f')->from('DeskPRO:Feedback', 'f');

                // we have to filter the user's requested types with what they
                // are allowed to access.
                $permissions_bag = $permissions_manager->getPortalPermissionsBag($person);
                $allowed_types = $permissions_bag->getAllowedFeedbackCategoryIds();
                $requested_types = $filter->getTypes();
                $types = array();
                if (null === $requested_types) {
                    $types = $allowed_types;
                } elseif (count($requested_types)) {
                    foreach ($requested_types as $req_type) {
                        if (in_array($req_type, $allowed_types)) {
                            $types[] = $req_type;
                        }
                    }
                }

                if (empty($types)) {
                    $types = $allowed_types;
                }

                $filter->setTypes($types);
                //
                // end filter types

                // status
                // "all","active","closed"
                switch ($filter->getStatus()) {
                    case FeedbackFilter::STATUS_ALL:
                        $valid_status = array(Feedback::STATUS_ACTIVE, Feedback::STATUS_CLOSED);
                        break;
                    case FeedbackFilter::STATUS_ACTIVE:
                        $valid_status = array(Feedback::STATUS_ACTIVE);
                        break;
                    case FeedbackFilter::STATUS_CLOSED:
                        $valid_status = array(Feedback::STATUS_CLOSED);
                        break;
                    default:
                        $valid_status = array();
                }
                $qb->where('f.status IN (:valid_status)')->setParameter('valid_status', $valid_status);

                // status_categories (feedback->status_category)
                // array(6,1,4)
                if (count($status_categories = $filter->getStatusCategories())) {
                    $qb->andWhere('f.status_category IN (:status_categories)')->setParameter(
                        'status_categories',
                        $status_categories
                    );
                }

                // types
                // array(1,3,5) $feedback->category
                if (count($types = $filter->getTypes())) {
                    $qb->andWhere('f.category IN (:types)')->setParameter('types', $types);
                }

                // sort
                // "date", "most-popular", "highest-rating", "most-discussed", "most-viewed"
                switch ($filter->getSort()) {
                    case FeedbackFilter::SORT_POPULARITY:
                    case FeedbackFilter::SORT_RATING:
                        $sort_string = 'f.total_rating';
                        break;
                    case FeedbackFilter::SORT_COMMENTS:
                        $sort_string = 'f.num_comments';
                        break;
                    case FeedbackFilter::SORT_VIEWS:
                        $sort_string = 'f.view_count';
                        break;
                    default:
                        $sort_string = 'f.date_created';
                }

                // sort direction
                // "desc" or "asc"
                $qb->orderBy($sort_string, $filter->getSortDirection());

                $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
                $pager->setMaxPerPage($max_per_page);
                $pager->setCurrentPage($page);

                return $pager;
            }
        );
    }

    /**
     * @param int|null|Feedback $item
     *
     * @return null|Feedback
     */
    public function getItem($item)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getItem',
                $item,
            ),
            function () use ($that, $item) {
                if (!$item) { // we need some input
                    return;
                }

                if ($item instanceof Feedback) { // already have what you seek
                    return $item;
                }

                return $that->getItemsRepo()->find($item);
            }
        );
    }

    public function getItemComments($item, Person $person = null)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getItemComments',
                $item,
                $person,
            ),
            function () use ($that, $item, $person) {
                $item = $that->getItem($item);

                return $that->getItemCommetRepo()->getDisplayComments($item, $person);
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return FeedbackCategory[]
     */
    public function getFeedbackCategoriesForPerson(Person $person)
    {
        $permissions_bag = $this->permissions_manager->getPortalPermissionsBag($person);

        return $this->getFeedbackCategoryRepo()->findBy(
            array(
                'id' => $permissions_bag->getAllowedFeedbackCategoryIds(),
            )
        );
    }

    /**
     * @param $status_category
     *
     * @return FeedbackStatusCategory
     */
    public function getFeedbackStatusCategory($status_category)
    {
        if ($status_category instanceof FeedbackStatusCategory) {
            return $status_category;
        }

        return $this->getFeedbackStatusCategoryRepo()->find($status_category);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Feedback
     */
    public function getItemsRepo()
    {
        return $this->em->getRepository('DeskPRO:Feedback');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\FeedbackStatusCategory
     */
    public function getFeedbackStatusCategoryRepo()
    {
        return $this->em->getRepository('DeskPRO:FeedbackStatusCategory');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\FeedbackCategory
     */
    public function getFeedbackCategoryRepo()
    {
        return $this->em->getRepository('DeskPRO:FeedbackCategory');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\FeedbackComment
     */
    public function getItemCommetRepo()
    {
        return $this->em->getRepository('DeskPRO:FeedbackComment');
    }
}
