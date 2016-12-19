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

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\CustomDefFeedback;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class FeedbackCategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/feedback_categories")
 */
class FeedbackCategoriesController extends BaseController
{
    /**
     * Fetch feedback categories list.
     * Proper output coming soon.
     *
     * @ApiDoc(
     *     section="Feedback",
     *     resourceDescription="Operations about feedback",
     *     description="get list of feedback categories",
     *     statusCodes={
     *         200="Returned if request was successful"
     *     },
     * )
     *
     * @Rest\Get("")
     * @Rest\View("list")
     *
     * @return View
     */
    public function listAction()
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('def.id', 'def.title')
            ->from(CustomDefFeedback::class, 'def')
            ->join('def.parent', 'parent')
            ->addSelect('def.title as title')
            ->addSelect('def.id as group_name')
            ->andWhere('parent.sys_name = :cat')
            ->setParameter('cat', 'cat')
            ->orderBy('def.title', 'asc')
        ;

        return View::create($this->wrap($qb->getQuery()->getResult()));
    }
}
