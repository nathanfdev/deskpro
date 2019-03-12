<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\TicketDeleted as TicketDeletedEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketNotFound.
 */
class TicketNotFound
{
    /**
     * Response status code.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $status;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $code = 'ticket_deleted';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $message = 'Ticket has been deleted';

    /**
     * @JMS\Type("map")
     *
     * @var array
     */
    private $detail;

    /**
     * @param TicketDeletedEntity $ticketDeleted
     */
    public function __construct(TicketDeletedEntity $ticketDeleted)
    {
        $newTicketId = $ticketDeleted->getNewTicketId();

        $this->status = $newTicketId
            ? Response::HTTP_MOVED_PERMANENTLY
            : Response::HTTP_NOT_FOUND;

        $this->detail = [
            'by_person'    => $ticketDeleted->getByPersonId(),
            'date_deleted' => $ticketDeleted->getDateCreated()->format('Y-m-d H:i:s'),
            'reason'       => $ticketDeleted->getReason(),
            'new_ticket'   => $newTicketId,
        ];
    }
}
