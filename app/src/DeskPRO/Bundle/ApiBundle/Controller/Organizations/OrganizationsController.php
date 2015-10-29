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
namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OrganizationsController.
 */
class OrganizationsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Count Organizations",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/organizations/counts", name="api_organizations_counts")
     */
    public function getCountAction()
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(o)')
           ->from('DeskPRO:Organization', 'o');
        $count = $qb->getQuery()->getSingleScalarResult();

        return View::create(
            $this->createRepresentation(Count::fromValue($count)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get all organizations list",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/organizations", name="api_organizations")
     */
    public function selectiveCollectionGetAction(Request $request)
    {
        $ids = explode(',', $request->get('ids', ''));
        $ids = array_map(function ($id) {return (int) $id;}, $ids);

        $qb = $this->getRepository(Organization::class)->createQueryBuilder('o');
        $qb->where('o.id IN (:ids)')
           ->setParameter('ids', $ids);

        return View::create(
            $this->dataSerialize($qb->getQuery()->getResult()),
            Response::HTTP_OK
        );
    }
}
