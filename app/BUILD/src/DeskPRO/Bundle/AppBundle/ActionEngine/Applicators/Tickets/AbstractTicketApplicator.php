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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\AbstractActionApplicator;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use Symfony\Component\Validator\Validator\RecursiveValidator;

abstract class AbstractTicketApplicator extends AbstractActionApplicator
{
    /** @var  TicketManager */
    protected $tm;
    /** @var  RecursiveValidator */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public function setTicketManager(TicketManager $tm)
    {
        $this->tm = $tm;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidator(RecursiveValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Ticket $ticket
     * @param string $actionName
     *
     * @throws \Exception
     */
    protected function saveTicket(Ticket $ticket, $actionName)
    {
        $this->validateTicket($ticket);

        $context = $this->tm->createAgentExecutorContext(null, $actionName, 'mass_actions');
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
            throw new ValidatorErrorsException($errors);
        }
    }
}
