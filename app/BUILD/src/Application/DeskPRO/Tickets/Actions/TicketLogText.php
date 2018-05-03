<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Sets a user variable.
 *
 * @option string  message
 * @option boolean is_html
 */
class TicketLogText extends AbstractAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('message');
        $options->addValidNames('is_html');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $value = $this->getActionOption('message', '');

        if ($this->getActionOption('is_html')) {
            $data = ['message_html' => $value];
        } else {
            $data = ['message' => $value];
        }

        $ticket->getStateChangeRecorder()->recordData('free', $data);
    }
}
