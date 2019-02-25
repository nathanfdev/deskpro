<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\CustomDataBilling;
use Application\DeskPRO\Entity\TicketCharge as TicketChargeEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketCharge.
 */
class TicketCharge
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

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
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $agent;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     *
     * @var \Application\DeskPRO\Entity\Organization
     */
    private $organization;

    /**
     * @JMS\Type("float")
     *
     * @var float
     */
    private $amount;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $chargeTime;

    /**
     * When the ticket charge was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @JMS\Type("deferred<string>")
     *
     * @var string
     */
    private $comment;

    /**
     * Custom persons data.
     *
     * @JMS\Type("deferred<custom_data<array>>")
     *
     * @var CustomDataBilling[]
     */
    protected $fields;

    /**
     * Constructor.
     *
     * @param TicketChargeEntity $entity
     */
    public function __construct(TicketChargeEntity $entity)
    {
        $this->id           = $entity->getId();
        $this->person       = $entity->getPerson();
        $this->ticket       = $entity->getTicket();
        $this->agent        = $entity->getAgent();
        $this->organization = $entity->getOrganization();
        $this->amount       = $entity->getAmount();
        $this->chargeTime   = $entity->getChargeTime();
        $this->dateCreated  = $entity->getDateCreated();
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function setCustomData($customData)
    {
        $this->fields = $customData;
    }
}
