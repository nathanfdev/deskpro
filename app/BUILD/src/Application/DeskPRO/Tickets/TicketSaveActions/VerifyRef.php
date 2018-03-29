<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\RefGenerator\RefGeneratorInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

class VerifyRef implements TicketSaveActionInterface
{
    /**
     * @var RefGeneratorInterface
     */
    private $ref_generator;

    /**
     * @param RefGeneratorInterface $ref_generator
     */
    public function __construct(RefGeneratorInterface $ref_generator)
    {
        $this->ref_generator = $ref_generator;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            return;
        }

        if (!$ticket->ref || (isset($ticket->__dp_is_autogen_ref) && $ticket->__dp_is_autogen_ref)) {
            $ticket->__dp_is_autogen_ref = false;
            try {
                $ticket->ref = $this->ref_generator->generateReference(Ticket::class);
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);

                $ref         = DpStrings::random(4, Strings::CHARS_ALPHA_IU).'-'.DpStrings::random(4, Strings::CHARS_NUM).'-'.DpStrings::random(4, Strings::CHARS_ALPHA_IU).'-'.date('ymd');
                $ticket->ref = $ref;
            }
        }
    }
}
