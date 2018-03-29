<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\TicketLog as TicketLogEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketLog.
 */
class TicketLog
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketLog>")
     *
     * @var \Application\DeskPRO\Entity\TicketLog
     */
    private $parent;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     *
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $actionType;

    /**
     * @JMS\Type("array")
     *
     * @var string
     */
    private $details = [];

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $messageHtml;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $messageText;

    /**
     * Constructor.
     *
     * @param TicketLogEntity $entity
     * @param string          $messageHtml
     */
    public function __construct(TicketLogEntity $entity, $messageHtml)
    {
        $this->id          = $entity->getId();
        $this->parent      = $entity->getParent();
        $this->ticket      = $entity->getTicket();
        $this->person      = $entity->getPerson();
        $this->actionType  = $entity->getActionType();
        $this->details     = $entity->getDetails();
        $this->dateCreated = $entity->getDateCreated();
        $this->messageHtml = $messageHtml;

        // convert html to text message
        $this->messageText = strip_tags($messageHtml);
        $this->messageText = str_replace("#\n#", ' ', $this->messageText);
        $this->messageText = preg_replace('#\s+#', ' ', $this->messageText);
        $this->messageText = trim($this->messageText);
    }
}
