<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class CommunityCategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_categories")
 */
class CommunityCategoriesController extends BaseController
{
    /**
     * Fetch custom community forums list.
     * Proper output coming soon.
     *
     * @ApiDoc(
     *     section="Community",
     *     resourceDescription="Operations about custom community forums",
     *     description="get list of custom community forums",
     *     statusCodes={
     *         200="Returned if request was successful"
     *     },
     *     output="Application\DeskPRO\Entity\CommunityForum"
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
            ->from(CustomDefCommunityTopic::class, 'def')
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
