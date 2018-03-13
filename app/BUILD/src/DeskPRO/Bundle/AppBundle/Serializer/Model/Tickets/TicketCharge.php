<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

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
}
