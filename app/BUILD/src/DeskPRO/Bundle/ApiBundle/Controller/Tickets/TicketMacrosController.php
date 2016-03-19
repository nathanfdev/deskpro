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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketMacrosController.
 *
 * @ApiModes("all")
 * @Route("/ticket_macros")
 */
class TicketMacrosController extends CrudController
{
    public static $exposeOnly = ['get', 'list'];
    public static $entity     = TicketMacro::class;
    public static $listOrder  = 'asc';

    /**
     * @Post("/{id}/apply/{ticket_id}")
     *
     * @param int     $id
     * @param int     $ticket_id
     * @param Request $request
     *
     * @return View
     */
    public function applyMacroAction($id, $ticket_id, Request $request)
    {
        $ticket  = $this->getTicketManager()->getTicket($ticket_id);
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

            $this->applyActions($actions->getUpdateActionsCollection(), $ticket, 'update');
            $this->applyActions($actions->getReplyActionsCollection(), $ticket, 'reply');

            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            return new View(null, Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->andWhere('e.is_global = 1 OR e.person = :user_id')
            ->setParameter('user_id', $this->getUser()->getId())
        ;
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
     * @param string            $event_type
     *
     * @throws ValidatorErrorsException
     */
    protected function applyActions(ActionsCollection $actions, Ticket $ticket, $event_type)
    {
        $actions->apply($ticket->getTicketLogger(), $ticket, $this->getUser());

        $validator = $this->get('validator');
        $errors    = $validator->validate($ticket, [
            new AppAssert\Ticket\TicketLayout(),
        ]);

        if ($errors->count() > 0) {
            throw new ValidatorErrorsException($errors);
        }

        $context = $this->getTicketManager()->createAgentExecutorContext($this->getUser(), $event_type, 'api');
        $this->getTicketManager()->saveTicket($ticket, $context);
    }

    /**
     * @return \Application\DeskPRO\Tickets\TicketManager
     */
    protected function getTicketManager()
    {
        return $this->getContainer()->getTicketManager();
    }
}
