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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\People\AgentProfileType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/agents")
 * @ApiDoc(target="all", section="Agents", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\BasePerson")
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\Person": "DeskPRO\Bundle\AppBundle\Serializer\Model\Person\BasePerson"
 * })
 * @ApiDoc(
 *     target="listAction",
 *     description="get list of agents",
 *     filters={
 *         {"name"="is_deleted", "pattern"="(1|0|-1)", "description"="deleted filter, defaults to 0", "dataType"="integer"},
 *         {"name"="online", "pattern"="(1|0|-1)", "description"="is online filter, defaults to 0", "dataType"="integer"},
 *         {"name"="online_for_chat", "pattern"="(1|0|-1)", "description"="is online for chat filter, defaults to 0", "dataType"="integer"}
 *     },
 *     statusCodes={
 *         200="OK"
 *     }
 * )
 */
class AgentsController extends AbstractPeopleController
{
    use TicketSaveTrait;

    public static $entity       = Person::class;
    public static $exposeOnly   = ['get', 'list', 'count', 'delete'];
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *     section="Agents",
     *     description="get a list of online agents",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<integer>"
     * )
     * @Rest\Get("/online", name="api_agents_online")
     *
     * @return View
     */
    public function getAgentsOnlineAction()
    {
        return View::create($this->wrap($this->get('data.agent')->getOnlineAgentIds()));
    }

    /**
     * @ApiDoc(
     *     section="Agents",
     *     description="get a list of agents, assigned to chats",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<Person>"
     * )
     * @Rest\Get("/assigned_to_chat")
     *
     * @return View
     */
    public function getAgentsOnChatsAction()
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('person')
            ->from(Person::class, 'person')
            ->join(ChatConversation::class, 'chat', Expr\Join::WITH, 'chat.agent = person')
        ;

        return View::create($this->wrap($qb->getQuery()->getResult()));
    }

    /**
     * @ApiDoc(
     *     section="Agents",
     *     description="remove agents permission (converts agent to user, unassigns tickets)",
     *     statusCodes={
     *         200="OK"
     *     }
     * )
     * @Rest\Delete("/{id}/agent_permissions")
     *
     * @param Request $request
     */
    public function deletePermissionsAction($id, Request $request)
    {
        $agent = $this->findEntity($id, $request);

        // Unassign tickets before setting $agent->setIsAgent(false) to prevent
        // error in the Application\DeskPRO\People\Helpers class

        /** @var Ticket[] $tickets */
        $tickets = $this->getRepository(Ticket::class)->findBy(compact('agent'));
        foreach ($tickets as $ticket) {
            $this->unassignTicket($ticket);
        }

        // Convert to user

        /* @var Person $agent */
        $agent->setIsAgent(false);
        $this->getManager()->persist($agent);
        $this->getManager()->flush();
    }

    /**
     * @ApiDoc(
     *     description="edit agent profile",
     *     statusCodes={
     *         204="No content"
     *     },
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\People\AgentProfileType",
     *      "options"={
     *          "data"="Application\DeskPRO\Entity\Person"
     *      }
     *     }
     * )
     *
     * @Rest\Put("/profile")
     *
     * @param Request $request
     *
     * @return View
     */
    public function editProfileAction(Request $request)
    {
        $form = $this->createForm(AgentProfileType::class, $this->getUser());
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($this->getUser());

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $qb->andWhere("$alias.is_agent = 1");
        $qb->select("partial $alias.{id,first_name,last_name,name,is_agent}");
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var Person $entity */
        $entity = parent::findEntity($id, $request);
        if (!$entity->isAgent()) {
            throw $this->createNotFoundException("This person isn't an agent");
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteEntity($entity)
    {
        $entity->setIsDeleted(true);
        $this->getManager()->persist($entity);
        $this->getManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        if ($attributes === PermissionGroupVoter::VIEW_LIST) {
            return;
        }

        parent::denyAccessUnlessGranted($attributes, $object, $message);
    }

    /**
     * @param Ticket $ticket
     */
    private function unassignTicket(Ticket $ticket)
    {
        $ticket->disableAutoTicketProcess();
        $ticket->setAgent(null);

        $this->saveTicket($ticket);
    }
}
