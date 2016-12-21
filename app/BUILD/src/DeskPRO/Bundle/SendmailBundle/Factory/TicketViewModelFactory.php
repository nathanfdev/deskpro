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

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewReplyRejectResolved;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketGuest;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketRegClosed;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketValidate;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketValidateEmail;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAddCc;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAutocloseWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAwaitingWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAwaitingWarnFinal;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewAutoreply;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewByAgent;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketParticipant;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketRate;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketReplyAutoreply;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketReplyByAgent;

class TicketViewModelFactory
{
    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * Constructor.
     *
     * @param ObjectRouter $objectRouter
     */
    public function __construct(ObjectRouter $objectRouter)
    {
        $this->objectRouter = $objectRouter;
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketNewAutoreply
     */
    public function createTicketNewAutoreplyModel(
        $ticket
    ) {
        return new TicketNewAutoreply($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketNewByAgent
     */
    public function createTicketNewByAgentModel(
        $ticket
    ) {
        return new TicketNewByAgent($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewTicketGuest
     */
    public function createNewTicketGuestModel(
        $ticket
    ) {
        return new NewTicketGuest($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewTicketValidate
     */
    public function createNewTicketValidateModel(
        $ticket
    ) {
        return new NewTicketValidate($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewTicketValidateEmail
     */
    public function createNewTicketValidateEmailModel(
        $ticket
    ) {
        return new NewTicketValidateEmail($this->objectRouter, $ticket);
    }

    /**
     * @return NewTicketRegClosed
     */
    public function createNewTicketRegClosedModel()
    {
        return new NewTicketRegClosed();
    }

    /**
     * @param Ticket        $ticket
     * @param TicketMessage $message
     *
     * @return TicketReplyByAgent
     */
    public function createTicketReplyByAgentModel(
        $ticket,
        $message
    ) {
        return new TicketReplyByAgent($this->objectRouter, $ticket, $message);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketReplyAutoreply
     */
    public function createTicketReplyAutoreplyModel(
        $ticket
    ) {
        return new TicketReplyAutoreply($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewReplyRejectResolved
     */
    public function createNewReplyRejectResolvedModel(
        $ticket
    ) {
        return new NewReplyRejectResolved($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAwaitingWarn
     */
    public function createTicketAwaitingWarnModel(
        $ticket
    ) {
        return new TicketAwaitingWarn($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAwaitingWarnFinal
     */
    public function createTicketAwaitingWarnFinalModel(
        $ticket
    ) {
        return new TicketAwaitingWarnFinal($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAutocloseWarn
     */
    public function createTicketAutocloseWarnModel(
        $ticket
    ) {
        return new TicketAutocloseWarn($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketRate
     */
    public function createTicketRateModel(
        $ticket
    ) {
        return new TicketRate($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAddCc
     */
    public function createTicketAddCcModel(
        $ticket
    ) {
        return new TicketAddCc($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketParticipant
     */
    public function createTicketParticipantModel(
        $ticket
    ) {
        return new TicketParticipant($this->objectRouter, $ticket);
    }
}
