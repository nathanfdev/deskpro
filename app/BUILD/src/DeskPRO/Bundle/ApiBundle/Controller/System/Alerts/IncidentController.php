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
use DeskPRO\Bundle\SystemBundle\Form\Type\SystemAlerts\IncidentType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IncidentController.
 *
 * @ApiModes("all")
 * @Rest\Route("/system/incidents")
 * @ApiDoc(target="all", section="System", output="DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident\StatefulIncident")
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
    public static $exposeOnly   = ['get', 'list', 'count', 'put', 'delete'];
    public static $listPaginate = false;

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

    /**
     * @ApiDoc(
     *      section="System",
     *      description="delete all incidents",
     *      statusCodes={
     *          200="Returned if success",
     *      }
     * )
     *
     * @Rest\Delete("")
     *
     * @return View
     */
    public function removeAllAction()
    {
        $this->getManager()->createQueryBuilder()->delete(AbstractIncident::class)->getQuery()->execute();

        return View::create(null, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      section="System",
     *      description="change dismissed status for all incidents",
     *      requirements={
     *          {
     *              "name"="dimissed",
     *              "requirement"="1|0",
     *              "description"="An integer representing bool"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if success",
     *      }
     * )
     *
     * @param Request $request
     *
     * @Rest\Put("")
     *
     * @return View
     */
    public function dismissAllAction(Request $request)
    {
        $this
            ->getManager()
            ->createQueryBuilder()
            ->update(AbstractIncident::class, 'i')
            ->set('i.dismissed', ':dismissed')
            ->getQuery()
            ->execute(['dismissed' => $request->request->get('dismissed')]);

        return View::create(null, Response::HTTP_OK);
    }
}
