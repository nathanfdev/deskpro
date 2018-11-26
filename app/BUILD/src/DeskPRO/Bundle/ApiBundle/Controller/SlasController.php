<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Sla;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\SearchHelper;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to SLAs.
 *
 * @ApiModes("all")
 * @Rest\Route("/slas")
 * @ApiDoc(target="all", section="SLAs", output="Application\DeskPRO\Entity\Sla")
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
     *     }
     * )
     *
     * @param Request $request
     * @Rest\Get("")
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
     *     requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of SLA",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     * @Rest\Get("/{id}", requirements={"id": "\d+"})
     * @Rest\View(serializerGroups={"Default", "details"})
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $context = new RequestQueryContext($qb, $alias, $request);
        SearchHelper::applyFieldFilter($context, 'search', 'title');
    }
}
