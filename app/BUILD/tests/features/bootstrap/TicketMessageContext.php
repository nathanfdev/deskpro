<?php

namespace DpBehat;

use Application\DeskPRO\Entity\TicketMessage;
use DpBehat\Data\DataContext;

class TicketMessageContext extends BaseContext
{
    /**
     * @Then the :messageId message should have :count attachments properly tagged
     *
     * @param int $messageId
     * @param int $count
     *
     * @throws \Exception
     */
    public function ticketMessageHasProperlyTaggedAttachments($messageId, $count)
    {
        $messageId = DataContext::replace($messageId);
        $message   = $this->repository(TicketMessage::class)->find($messageId);
        if (!$message) {
            throw new \Exception("Can't find ticket message: $messageId");
        }

        if (count($message->getAttachments()) !== $count) {
            throw new \Exception("Wrong attachment count. Expected $count, real: ".count($message->getAttachments()));
        }

        foreach ($message->getAttachments() as $ticketAttachment) {
            if (!$ticketAttachment->getBlob()->isTicketAttachment()) {
                throw new \Exception("Blob with auth code ({$ticketAttachment->getBlob()->getAuthcode()}) not tagged as ticket attachment");
            }
        }
    }
}
