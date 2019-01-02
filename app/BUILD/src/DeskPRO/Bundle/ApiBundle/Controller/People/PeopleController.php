<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\CustomDataHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\LabelHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\ListHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\RequestQueryContext;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\SearchHelper;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\UsergroupsHelper;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class PeopleController.
 *
 * @ApiModes("all")
 * @Rest\Route("/people")
 * @ApiDoc(target="all", section="People", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {
 *              "name"="order_by",
 *              "description"="people list sort",
 *              "pattern"="id|date_created|date_last_login|name|first_name|last_name|primary_email|timezone|organization",
 *              "dataType"="string",
 *          },
 *          {"name"="order_dir", "description"="list sort order", "dataType"="string", "pattern"="asc|desc"},
 *          {"name"="primary_email", "description"="primary email filter", "dataType"="\w+"},
 *          {"name"="organization", "description"="Comma separated list of IDs", "dataType"="[\d+,]+"},
 *          {"name"="is_agent", "description"="agents filter", "dataType"="boolean"},
 *          {"name"="is_deleted", "pattern"="(1|0|-1)", "description"="deleted filter, defaults to 0", "dataType"="integer"},
 *          {"name"="online", "pattern"="(1|0|-1)", "description"="is online filter, defaults to 0", "dataType"="integer"},
 *          {"name"="online_for_chat", "pattern"="(1|0|-1)", "description"="is agent and online for chat filter, defaults to 0", "dataType"="integer"},
 *          {"name"="not_me", "description"="exclude yourself filter", "dataType"="boolean"},
 *          {"name"="agent_team", "description"="agent teams filter", "dataType"="array|integer|null", "pattern"="[\d+,]+"},
 *          {"name"="user_group", "description"="usergroups filter", "dataType"="array|integer|null", "pattern"="[\d+,]+"},
 *          {"name"="label", "description"="labels filter option", "dataType"="array", "pattern"="[\w+,]+"},
 *          {"name"="search", "description"="search filter (on name)", "dataType"="string", "pattern"="\w+"},
 *          {
 *              "name"="person_field.{id}",
 *              "description"="
 *                  Custom person field filter. To filter by a custom field with ID=1 you need to add
 *                  ?person_field.1=value to the query string",
 *              "dataType"="string",
 *              "pattern"="\d+|\w"
 *          }
 *     }
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\People\PersonType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class PeopleController extends AbstractPeopleController
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
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
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
        $person   = $this->findEntity($id, $request);
        $personId = $person->getId();
        $options  = ['not_status' => [Ticket::HIDDEN_STATUS_DELETED, Ticket::HIDDEN_STATUS_SPAM]];

        if ($person->isAgent()) {
            $options['agent'] = $personId;
        } else {
            $options['person'] = $personId;
        }

        return TicketsController::subRequestSearch($this->getKernel(), $request, $options);
    }

    /**
     * @ApiDoc(
     *     section="People",
     *     description="adds permissions (for now only accepts {agent: true} to add agent permissions)",
     *     statusCodes={
     *         200="OK"
     *     },
     *     parameters={
     *        {"name"="agent", "description"="set as agent", "dataType"="boolean", "required"=false}
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
        $permissions = $request->request->all();
        if (array_key_exists('agent', $permissions) && $permissions['agent']) {
            $person->setIsAgent(true);
        }

        $this->getManager()->persist($person);
        $this->getManager()->flush();
    }

    /**
     * @ApiDoc(
     *     section="People",
     *     description="Clear all session data",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="The id of the resource",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *        204="OK"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     * @Rest\Post("/{id}/sessions/clear")
     *
     * @param         $id
     * @param Request $request
     *
     * @return View
     */
    public function clearSessionAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupEntityContext($id, $request));

        $person = $this->findEntity($id, $request);

        $this->getManager()->getConnection()->executeUpdate(
            'DELETE FROM sessions WHERE person_id = :person_id',
            [
                'person_id' => $person->getId(),
            ]
        );
        $this->getManager()->getConnection()->executeUpdate(
            'DELETE FROM sess_data WHERE person_id = :person_id',
            [
                'person_id' => $person->getId(),
            ]
        );

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $context = new RequestQueryContext($qb, $alias, $request);

        DateHelper::applyDatePeriodFilter($context, 'date_created', 'period_created');
        UsergroupsHelper::applyUsergroupsFilters($context);
        ListHelper::applyInListFilter($context, 'organization');
        LabelHelper::applyLabelFilters($context, static::$entity);
        CustomDataHelper::applyCustomDataFilters($context, 'person', CustomDefPerson::class);
        SearchHelper::applyFieldFilter($context, 'search', 'name');

        if (null !== $request->get('is_agent')) {
            $qb->andWhere("$alias.is_agent = :is_agent");
            $qb->setParameter('is_agent', (int) $request->get('is_agent'));
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
        if (null !== $request->get('usergroup')) {
            $usergroup = (int) $request->get('usergroup');
            $qb->leftJoin("$alias.usergroups", 'usergroups');
            if ($usergroup > 0) {
                $qb->andWhere('usergroups.id = :usergroup_id');
                $qb->setParameter('usergroup_id', $usergroup);
            } else {
                $qb->andWhere('usergroups.id IS NULL');
            }
        }

        if (null !== $request->get('primary_email')) {
            $email = $request->get('primary_email');
            $qb->leftJoin("$alias.primary_email", 'primary_email');
            $qb->andWhere('primary_email.email = :email');
            $qb->setParameter('email', $email);
        }

        $emails = $request->get('emails');
        if (null !== $emails) {
            $emails = (array) $emails;
            $qb->leftJoin("$alias.emails", 'emails');
            $qb->andWhere('emails.email IN (:emails)');
            $qb->setParameter('emails', $emails);
        }

        if (null !== $request->get('department')) {
            $department = (int) $request->get('department');
            $qb->leftJoin("$alias.department_permissions", 'department_permissions');
            $qb->leftJoin("$alias.usergroups", 'usergroups');
            $qb->leftJoin('usergroups.department_permissions', 'usergroup_department_permissions');

            if ($department > 0) {
                $qb->andWhere('department_permissions.department = :department OR usergroup_department_permissions.department = :department');
                $qb->setParameter('department', $department);
            } else {
                $qb->andWhere('department_permissions.department IS NULL');
                $qb->andWhere('usergroup_department_permissions.department IS NULL');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applySorting(QueryBuilder $qb, $alias, Request $request)
    {
        $order = strtolower($request->get('order_dir'));
        if ($order && !in_array($order, ['asc', 'desc'])) {
            throw $this->createBadRequestException('Unknown order value');
        }

        $sortParam = strtolower($request->get('order_by'));
        if ($sortParam === 'organization') {
            $sort = 'organization.name';

            $qb->leftJoin("$alias.organization", 'organization');
            $qb->orderBy($sort, $order);
        } elseif ($sortParam === 'primary_email') {
            $sort = 'primary_email.email';

            $qb->leftJoin("$alias.primary_email", 'primary_email');
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
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'agent_interface' => true,
        ]);

        return parent::handleForm($model, $request, $options);
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
