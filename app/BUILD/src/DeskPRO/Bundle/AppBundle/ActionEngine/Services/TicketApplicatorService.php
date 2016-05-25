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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Services;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class TicketApplicatorService extends AbstractApplicatorService
{
    protected $class     = Ticket::class;
    protected $namespace = 'Tickets';

    /** @var  EntityManager */
    protected $em;

    /** @var  TicketManager */
    protected $tm;

    /** @var RecursiveValidator $validator */
    protected $validator;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->tm        = $container->get('ticket_manager');
        $this->validator = $container->get('validator');
        parent::__construct($container->get('doctrine.orm.default_entity_manager'));
    }

    /**
     * {@inheritdoc}
     */
    protected function createApplicator($actionName)
    {
        $applicator = $this->container
            ->get('action_engine.tickets.'.$actionName, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if (null === $applicator) {
            $applicator = parent::createApplicator($actionName);
        }
        $applicator->setTicketManager($this->tm);
        $applicator->setValidator($this->validator);

        return $applicator;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities(array $ids)
    {
        /** @var Ticket[] $tickets */
        $tickets = parent::getEntities($ids);
        foreach ($tickets as $ticket) {
            $ticket->disableAutoTicketProcess();
        }

        return $tickets;
    }

    /**
     * @param Ticket $ticket
     *
     * @throws \Exception
     */
    protected function saveObject($ticket)
    {
        $this->validateTicket($ticket);

        $context = $this->tm->createAgentExecutorContext(null, 'mass_actions', 'api');
        $this->tm->saveTicket($ticket, $context);
    }

    /**
     * @param Ticket $ticket
     */
    protected function validateTicket(Ticket $ticket)
    {
        $errors = $this->validator->validate(
            $ticket,
            [new AppAssert\Ticket\TicketLayout(['context' => 'agent'])]
        );

        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                echo $error;
            }
            throw new ValidatorErrorsException($errors);
        }
    }
}
