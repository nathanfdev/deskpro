<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use DpTest\ApiTestCase;

class TicketAttachmentTest extends ApiTestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to add attachment, no message is referred to.
     */
    public function test_cascade_persist_exception()
    {
        $ticket = new Ticket();

        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();

        $blob = new Blob();
        $blob->setFilename('file.txt');
        $blob->setIsTemp(false);
        $blob->setContentType('plain/text');
        $blob->setBlobHash('aaaaa');

        $this->getEntityManager()->persist($blob);
        $this->getEntityManager()->flush();

        $ticketMessage = new TicketMessage();
        $ticketMessage->setMessage('message');
        $ticket->addMessage($ticketMessage);

        $attachment = new TicketAttachment();
        $attachment->setBlob($blob);

        $this->getEntityManager()->persist($attachment);
    }
}
