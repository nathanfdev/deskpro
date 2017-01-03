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

namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\CustomDataHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\LabelHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\UsergroupsHelper;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Organizations\OrganizationType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrganizationsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organizations")
 * @ApiDoc(target="all", section="Organizations", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Organization\Organization")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="period_created", "description"="period created filter", "dataType"="string", "pattern"="\w+"},
 *          {"name"="user_group", "description"="usergroups filter", "dataType"="array|integer|null", "pattern"="[\d+,]+"},
 *          {"name"="labels", "description"="labels filter option", "dataType"="array", "pattern"="[\w+,]+"},
 *          {
 *              "name"="org_field.{id}",
 *              "description"="
 *                  Custom organization field filter. To filter by a custom field with ID=1 you need to add
 *                  ?org_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *     }
 * )
 */
class OrganizationsController extends CrudController
{
    public static $entity      = Organization::class;
    public static $type        = OrganizationType::class;
    public static $sortOptions = [
        'date_created' => 'date_created',
        'id'           => 'id',
        'name'         => 'name',
        'summary'      => 'summary',
        'importance'   => 'importance',
        'parent'       => ['join' => 'parent', 'as' => 'po', 'sort' => 'po.id'],
    ];
    public static $listSort  = 'date_created';
    public static $listOrder = 'desc';

    /**
     * @ApiDoc(
     *     section="Organizations",
     *     description="Get tickets of the given organization",
     *     statusCodes={
     *         200="Everything is OK",
     *         400="Malformed request"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     * @Rest\Get("/{id}/tickets")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTicketsAction(Request $request, $id)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, ['organization' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);

        DateHelper::applyDatePeriodFilter($context, 'date_created', 'period_created');
        UsergroupsHelper::applyUsergroupsFilters($context);
        LabelHelper::applyLabelFilters($context, static::$entity);
        CustomDataHelper::applyCustomDataFilters($context, 'org', CustomDefOrganization::class);

        // parent filters
        ListHelper::applyInListFilter($context, 'parent');

        if ($request->query->has('is_child')) {
            if ($request->query->get('is_child')) {
                $qb->andWhere("$alias.parent IS NOT NULL");
            } else {
                $qb->andWhere("$alias.parent IS NULL");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        if ($groupBy === 'user_group') {
            UsergroupsHelper::applyUserGroupsGroupBy(new RequestQueryContext($qb, $alias, $request));
        }
    }
}
