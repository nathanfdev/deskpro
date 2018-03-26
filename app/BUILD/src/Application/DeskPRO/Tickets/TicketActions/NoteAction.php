<?php

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Ticket;

/**
 * Class NoteAction.
 */
class NoteAction extends ReplyAction
{
    /**
     * @var bool
     */
    protected $is_note = true;

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            [
                'action'     => 'note',
                'reply_text' => $this->reply_text,
                'attach_ids' => $this->attach_ids,
                'is_html'    => $this->is_html,
                'is_note'    => true,
                'person_id'  => $this->person_id,
            ],
        ];
    }
}
