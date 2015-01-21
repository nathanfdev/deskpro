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
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use Application\PortalBundle\Model\FeedbackFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackDataService
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
     * @param $page
     * @param $max_per_page
     * @param FeedbackFilter $filter
     * @return Pagerfanta
     */
    public function getItemsPager($page, $max_per_page, FeedbackFilter $filter)
    {
        // TODO: two tags use this on the same request (list, and pager). So, need to hash the inputs and
        // keep the computed $pager in memory for cases of asking for the exact same pager twice.

        $qb = $this->em->createQueryBuilder();
        $qb->select('f')->from('DeskPRO:Feedback', 'f');

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
            $qb->andWhere('f.status_category IN (:status_categories)')->setParameter('status_categories', $status_categories);
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

    /**
     * @param int|null|Feedback $item
     * @return null|Feedback
     */
    public function getItem($item)
    {
        if (!$item) { // we need some input
            return null;
        }

        if ($item instanceof Feedback) { // already have what you seek
            return $item;
        }

        return $this->getItemsRepo()->find($item);
    }

    public function getItemComments($item, Person $person = null)
    {
        $item = $this->getItem($item);

        return $this->getItemCommetRepo()->getDisplayComments($item, $person);
    }

    /**
     * @param Person $person
     * @return FeedbackCategory[]
     */
    public function getFeedbackCategoriesForPerson(Person $person)
    {
        // TODO: permissions
        return $this->getFeedbackCategoryRepo()->findAll();
    }

    /**
     * @param $status_category
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
 