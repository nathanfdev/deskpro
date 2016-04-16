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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Sla;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to languages.
 *
 * @ApiModes("all")
 * @Rest\Route("/slas")
 * @ApiDoc(target="all", section="Slas", output="Application\DeskPRO\Entity\Sla")
 */
class SlasController extends CrudController
{
    public static $entity       = Sla::class;
    public static $listOrder    = 'desc';
    public static $exposeOnly   = ['list', 'get'];
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *     section="SLAs",
     *     resourceDescription="Operations about SLAs",
     *     tags={"CRUD"="#ffa500"},
     *     description="get SLAs collection",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="array<Application\DeskPRO\Entity\Sla>"
     * )
     *
     * @param Request $request
     * @Rest\Get("", name="slas_list")
     * @Rest\View(serializerGroups={"Default"})
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * @ApiDoc(
     *     section="SLAs",
     *     resourceDescription="Operations about SLAs",
     *     tags={"CRUD"="#ffa500"},
     *     description="get SLA",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="Application\DeskPRO\Entity\Sla"
     * )
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     * @Rest\Get("/{id}", name="sla_view", requirements={"id": "\d+"})
     * @Rest\View(serializerGroups={"Default", "details"})
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }
}
