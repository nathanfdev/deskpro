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

namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;
use Doctrine\ORM\Query\QueryException;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackCommentsDataService extends AbstractDataService
{
    /**
     * Select filtered list of feedback.
     *
     * @param FeedbackCommentsSelectCriteria $criteria
     * @param int                            $page
     * @param int                            $count
     *
     * @return array
     */
    public function selectComments(FeedbackCommentsSelectCriteria $criteria, $page, $count)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->innerJoin('c.feedback', 'feedback')
            ->leftJoin('feedback.status_category', 'statusCategory')
            ->leftJoin('feedback.custom_data', 'customCat')
            ->leftJoin('feedback.labels', 'labels')
            ->leftJoin('feedback.category', 'category')
            ->leftJoin('feedback.person', 'person');
        $criteria->applyFilters($qb);
        $criteria->applySorting($qb);

        $comments = $qb->getQuery()->getResult();

        $pager = new Pagerfanta(new ArrayAdapter($comments));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @return Count
     */
    public function countAwaitingValidation()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('count(c)')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->where('c.is_reviewed = 0');
        try {
            $count = $qb->getQuery()->getSingleScalarResult();
        } catch (QueryException $e) {
            $count = 0;
        }

        return Count::fromValue($count);
    }
}
