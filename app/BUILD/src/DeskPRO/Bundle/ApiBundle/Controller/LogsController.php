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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LogsController.
 *
 * @ApiModes("all")
 */
class LogsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get api logs collection",
     *      statusCodes={
     *          200="Success",
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\ApiLog"
     * )
     *
     * @param Request $request
     * @Annotations\Get("/api_logs", name="api_logs_list")
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $repository = $this->get('doctrine.orm.default_entity_manager')->getRepository('\DeskPRO\Bundle\AppBundle\Entity\ApiLog');
        $qb         = $repository->createQueryBuilder('a')->select('a');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage(50);
        $pager->setCurrentPage($request->query->getInt('page', 1));

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }
}
