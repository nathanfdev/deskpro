<?php

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
 *     target="putAction,dismissAllAction",
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
