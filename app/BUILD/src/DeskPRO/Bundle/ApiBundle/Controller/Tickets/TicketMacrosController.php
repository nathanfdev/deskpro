<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketMacrosController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_macros")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketMacro")
 */
class TicketMacrosController extends CrudController
{
    use TicketSaveTrait;

    public static $exposeOnly = ['get', 'list'];
    public static $entity     = TicketMacro::class;
    public static $listOrder  = 'asc';

    /**
     * Apply macro with given id to the specified ticket.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     description="apply macro to ticket",
     *     statusCodes={
     *         204="Everything is OK",
     *         403="User is not allowed to modify the ticket",
     *         404={
     *             "Ticket wasn't found",
     *             "Macro wasn't found",
     *         }
     *     },
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "dataType"="integer", "description"="the macro identity"},
     *         {"name"="ticketId", "requirement"="\d+", "dataType"="integer", "description"="the ticket identity"},
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/{id}/apply/{ticket}")
     *
     * @param int     $id
     * @param Ticket  $ticket
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function applyMacroAction($id, Ticket $ticket, Request $request)
    {
        $macro   = $this->findEntity($id, $request);
        $actions = $macro->getActionsCollection();

        if (!$ticket) {
            throw $this->createNotFoundException();
        }
        if (!$actions->applyCheckPermission($ticket, $this->getUser())) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getManager();

        try {
            $em->beginTransaction();
            $ticket->disableAutoTicketProcess();

            $this->applyActions($actions->getUpdateActionsCollection(), $ticket);
            $this->applyActions($actions->getReplyActionsCollection(), $ticket);

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw $e;
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere('e.is_global = 1 OR e.person = :user_id');
        $qb->setParameter('user_id', $this->getUser()->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var TicketMacro $entity */
        $entity = parent::findEntity($id, $request);
        if (!$entity->getIsGlobal() && $entity->getPerson() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }

    /**
     * @param ActionsCollection $actions
     * @param Ticket            $ticket
     *
     * @throws ValidatorErrorsException
     */
    protected function applyActions(ActionsCollection $actions, Ticket $ticket)
    {
        $actions->apply($ticket->getTicketLogger(), $ticket, $this->getUser());

        $validator = $this->get('validator');
        $errors    = $validator->validate($ticket, [
            new AppAssert\Ticket\TicketLayout([
                'context' => 'agent',
            ]),
        ]);

        if ($errors->count() > 0) {
            throw new ValidatorErrorsException($errors);
        }

        $this->saveTicket($ticket);
    }
}
