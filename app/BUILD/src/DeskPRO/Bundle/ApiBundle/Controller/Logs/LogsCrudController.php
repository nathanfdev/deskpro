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
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
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
namespace DeskPRO\Bundle\ApiBundle\Controller\Logs;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogsCrudController.
 *
 * @ApiDocSection("Logs")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Entity\ApiLog")
 * @ApiModes("all")
 * @Rest\Route("/api_logs")
 */
class LogsCrudController extends CrudController
{
    public static $entity         = ApiLog::class;
    public static $listPaginate   = true;
    public static $listMaxResults = 1000;
    public static $listPerPage    = 100;
    public static $exposeOnly     = ['list', 'get'];

    /**
     * @ApiDoc(
     *     section="Logs",
     *     resourceDescription="Operations about logs",
     *     tags={"CRUD"="#ffa500"},
     *     description="get api logs collection",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\ApiLog>"
     * )
     *
     * @param Request $request
     * @Rest\Get("", name="api_logs_list")
     * @Rest\View(serializerGroups={"list"})
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * @ApiDoc(
     *     section="Logs",
     *     resourceDescription="Operations about logs",
     *     tags={"CRUD"="#ffa500"},
     *     description="get api log",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\ApiLog"
     * )
     *
     * @param int $id
     * @Rest\Get("/{id}", name="api_logs_view", requirements={"page": "\d+"})
     * @Rest\View(serializerGroups={"list", "details"})
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }
}
