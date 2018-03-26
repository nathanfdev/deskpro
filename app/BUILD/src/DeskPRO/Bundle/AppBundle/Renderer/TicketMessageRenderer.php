<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\Entity\TicketMessage;

class TicketMessageRenderer
{
    /**
     * @var InlineAttachmentsRenderer
     */
    private $inline_attach;

    /**
     * @param InlineAttachmentsRenderer $inline_attach
     */
    public function __construct(InlineAttachmentsRenderer $inline_attach)
    {
        $this->inline_attach = $inline_attach;
    }

    /**
     * @param TicketMessage $message
     *
     * @return string
     */
    public function render(TicketMessage $message)
    {
        return $this->inline_attach->render($message->message);
    }
}
