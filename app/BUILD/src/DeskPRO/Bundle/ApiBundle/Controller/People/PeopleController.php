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
namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\LabelHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\UsergroupsHelper;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class PeopleController.
 *
 * @ApiModes("all")
 * @Rest\Route("/people")
 * @ApiDoc(target="all", section="People", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
 */
class PeopleController extends CrudController
{
    public static $entity      = Person::class;
    public static $type        = PersonType::class;
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
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(
            array_merge($params, $masterRequest->query->all()),
            null,
            ['_controller' => 'ApiBundle:People\People:list']
        );
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @ApiDoc(
     *      description="Get tickets of the given person",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Rest\Get("/{id}/tickets")
     *
     * @param Request $request
     * @param int     $id      Person ID
     *
     * @return Response
     */
    public function getTicketsAction(Request $request, $id)
    {
        /** @var Person $person */
        $person = $this->findEntity($id, $request);

        if (null === $person) {
            throw new NotFoundHttpException("Person with ID=$id was not found");
        }

        $options  = ['not-status' => [Ticket::HIDDEN_STATUS_DELETED, Ticket::HIDDEN_STATUS_SPAM]];
        $personId = $person->getId();

        if ($person->isAgent()) {
            $options['agent'] = $personId;
        } else {
            $options['person-advanced']['person'] = $personId;
            if ($person->isOrganizationManager()) {
                $options['person-advanced']['org'] = $person->getOrganization()->getId();
            }
        }

        return TicketsController::subRequestSearch($this->getKernel(), $request, $options);
    }

    // This exists temporarily until we have some real versioned actions ###############################################

    public function getTickets20151231Action()
    {
        die('v 20151231');
    }

    // #################################################################################################################

    /**
     * @ApiDoc(
     *     section="People",
     *     description="adds permissions (for now only accepts {agent: true} to add agent permissions)",
     *     statusCodes={
     *         200="OK"
     *     }
     * )
     * @Rest\Put("/{id}/permissions")
     *
     * @param         $id
     * @param Request $request
     */
    public function updatePermissionsAction($id, Request $request)
    {
        $person      = $this->findEntity($id, $request);
        $permissions = $this->getRequestContent($request);
        if (array_key_exists('agent', $permissions) && $permissions['agent'] === true) {
            $person->setIsAgent(true);
        }
        $this->getManager()->persist($person);
        $this->getManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $context = new RequestQueryContext($qb, $alias, $request);

        DateHelper::applyDatePeriodFilter($context, 'date_created', 'period_created');
        UsergroupsHelper::applyUsergroupsFilters($context);
        ListHelper::applyInListFilter($context, 'organization');
        LabelHelper::applyLabelFilters($context, static::$entity);

        if (null !== $request->get('is_agent')) {
            $qb->andWhere("$alias.is_agent = :is_agent");
            $qb->setParameter('is_agent', (int) $request->get('is_agent'));
        }

        if (null !== $request->get('is_deleted')) {
            $qb->andWhere("$alias.is_deleted = :is_deleted");
            $qb->setParameter('is_deleted', (int) $request->get('is_deleted'));
        }

        if ($request->get('not_me')) {
            $qb->andWhere("$alias.id != :id");
            $qb->setParameter('id', $this->getUser()->getId());
        }

        if (null !== $request->get('agent_team')) {
            $agentTeam = (int) $request->get('agent_team');
            $qb->leftJoin("$alias.teams", 'teams');
            if ($agentTeam > 0) {
                $qb->andWhere('teams.id = :agent_team_id');
                $qb->setParameter('agent_team_id', $agentTeam);
            } else {
                $qb->andWhere('teams.id IS NULL');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting(QueryBuilder $qb, $alias, Request $request)
    {
        $sortParam = strtolower($request->get('order_by'));
        if ($sortParam === 'organization') {
            $sort  = 'organization.name';
            $order = strtolower($request->get('order_dir'));

            if ($order && !in_array($order, ['asc', 'desc'])) {
                throw $this->createBadRequestException('Unknown order value');
            }

            $qb->leftJoin("$alias.organization", 'organization');
            $qb->orderBy($sort, $order);
        } else {
            parent::applySorting($qb, $alias, $request);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
    {
        switch ($groupBy) {
            case 'user_group':
                UsergroupsHelper::applyUserGroupsGroupBy(new RequestQueryContext($qb, $alias, $request));

                break;
            case 'agent_team':
                $qb
                    ->leftJoin("$alias.teams", 'teams')
                    ->addSelect('teams.name as title')
                    ->addSelect('teams.id as group_name')
                    ->groupBy('group_name');

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteEntity($entity)
    {
        if ($entity->isAgent()) {
            throw $this->createBadRequestException("You can't delete an agent via 'people' API endpoint, use 'agents'");
        }

        parent::deleteEntity($entity);
    }
}
