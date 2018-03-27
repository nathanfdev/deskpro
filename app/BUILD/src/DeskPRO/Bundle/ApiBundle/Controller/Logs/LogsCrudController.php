<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Logs;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogsCrudController.
 *
 * @ApiModes("all")
 * @Rest\Route("/api_logs")
 * @ApiDoc(target="all", section="Logs", output="DeskPRO\Bundle\AppBundle\Entity\ApiLog")
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
     * @Rest\Get("", name="api_logs_list")
     * @Rest\View(serializerGroups={"list"})
     *
     * @param Request $request
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
     *
     * @Rest\Get("/{id}", name="api_logs_view", requirements={"page": "\d+"})
     * @Rest\View(serializerGroups={"list", "details"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }
}
