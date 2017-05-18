<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
