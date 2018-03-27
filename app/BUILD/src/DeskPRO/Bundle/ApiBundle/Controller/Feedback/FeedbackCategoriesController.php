<?php

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
     *     output="Application\DeskPRO\Entity\FeedbackCategory"
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
