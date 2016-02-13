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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Labels\LabelsHelper;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PeopleController.
 *
 * @ApiModes("all")
 * @Route("/people")
 */
class PeopleController extends CrudController
{
    use LabelsHelper;

    public static $entity      = Person::class;
    public static $type        = 'api_person';
    public static $sortOptions = [
        'date_created'    => 'date_created',
        'date_last_login' => 'date_last_login',
        'id'              => 'id',
        'name'            => 'name',
        'first_name'      => 'first_name',
        'last_name'       => 'last_name',
        'primary_email'   => 'primary_email',
        'timezone'        => 'timezone',
        'organization'    => 'organization',
    ];
    public static $listSort  = 'date_created';
    public static $listOrder = 'desc';

    /**
     * @ApiDoc(
     *      description="Get tickets of the given person",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/{id}/tickets")
     */
    public function getTicketsAction(Request $request, $id)
    {
        return TicketsController::subRequestSearch($this->get('kernel'), $request, ['person' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if (null !== $request->get('is_agent')) {
            $qb->andWhere("$alias.is_agent = :is_agent");
            $qb->setParameter('is_agent', (int) $request->get('is_agent'));
        }

        if (null !== $request->get('is_deleted')) {
            $qb->andWhere("$alias.is_deleted = :is_deleted");
            $qb->setParameter('is_deleted', (int) $request->get('is_deleted'));
        }

        if ($request->get('not_me')) {
            $user = $this->getUser();
            $qb->andWhere("$alias.id != :id");
            $qb->setParameter('id', $user->getId());
        }
        if ($period = $request->get('period_created')) {
            $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
            $qb->andWhere("$datePeriodCaseWhen = :period_created");
            $qb->setParameter('period_created', $period);
        }
        $userGroups = $request->get('user_group');
        if (null !== $userGroups) {
            $qb->leftJoin("$alias.usergroups", 'ug');
            if (is_array($userGroups)) {
                $qb
                    ->andWhere('ug.id in (:user_group_id)')
                    ->setParameter('user_group_id', $userGroups);
            } else {
                $user_group = (int) $userGroups;
                if ($user_group > 0) {
                    $qb
                        ->andWhere('ug.id = :user_group_id')
                        ->setParameter('user_group_id', $user_group);
                } else {
                    $qb->andWhere('ug.id IS NULL');
                }
            }
        }

        if (null !== $request->get('agent_team')) {
            $agent_team = (int) $request->get('agent_team');
            $qb->leftJoin("$alias.teams", 'teams');
            if ($agent_team > 0) {
                $qb
                    ->andWhere('teams.id = :agent_team_id')
                    ->setParameter('agent_team_id', $agent_team);
            } else {
                $qb->andWhere('teams.id IS NULL');
            }
        }

        if ($org = $request->get('organization')) {
            $qb->innerJoin("$alias.organization", 'org');
            if (is_array($org)) {
                $qb->andWhere('org.id IN (:org)');
            } else {
                $qb->andWhere('org.id = :org');
            }
            $qb->setParameter('org', $org);
        }
    }

    /**
     * Apply 'sort' and 'order' depending on static::$sortOptions.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applySorting(
        QueryBuilder $qb,
        $alias,
        Request $request
    ) {
        if (is_array(static::$sortOptions)) {
            $sortParam = strtolower($request->get('sort'));
            if ($sortParam && !array_key_exists($sortParam, static::$sortOptions)) {
                throw $this->createBadRequestException('Unknown sort field');
            }
            if ($sortParam === 'organization') {
                $qb->leftJoin("$alias.organization", 'organization');
                $sort  = 'organization.name';
                $order = strtolower($request->get('order'));
                if ($order && !in_array($order, ['asc', 'desc'])) {
                    throw $this->createBadRequestException('Unknown order value');
                }
                $qb->orderBy($sort, $order);
            } else {
                parent::applySorting($qb, $alias, $request);
            }
        }
    }
}
