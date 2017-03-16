<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\System\Alerts;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\Form\Type\SystemAlerts\IncidentType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IncidentController.
 *
 * @ApiModes("all")
 * @Rest\Route("/system/incidents")
 * @ApiDoc(target="all", section="System")
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\SystemBundle\Form\Type\SystemAlerts\IncidentType"
 *     }
 * )
 */
class IncidentController extends CrudController
{
    public static $entity       = AbstractIncident::class;
    public static $type         = IncidentType::class;
    public static $exposeOnly   = ['get', 'list', 'put', 'delete'];
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *     section="System",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident"
     * )
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     */
    public function getAction(Request $request, $id)
    {
        /** @var Incident $incident */
        if (!$incident = $this->findEntity($id, $request)) {
            throw $this->createNotFoundException();
        }

        $instructionsHtml = $this->get('dp_sys.alerts.instructions_generator')->generate($incident);

        return View::create($this->wrap([
            'incident'          => $incident,
            'instructions_html' => $instructionsHtml,
        ]));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->andWhere("$alias.raised = true")
            ->addOrderBy("$alias.dismissed", 'asc')
            ->addOrderBy("$alias.id", 'desc')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager('system');
    }
}
