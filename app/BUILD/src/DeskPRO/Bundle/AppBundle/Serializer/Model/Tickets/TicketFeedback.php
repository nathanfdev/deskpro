<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFeedback as TicketFeedbackEntity;
use JMS\Serializer\Annotation as JMS;

class TicketFeedback
{
    /**
     * Satisfaction message.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $message;

    /**
     * Satisfaction rating (1, 0 or -1).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $rating;

    /**
     * Satisfaction rating type ('positive', 'neutral' or 'negative').
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ratingType;

    /**
     * Date when satisfaction was submitted.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Person this satisfaction was sent by.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    public function __construct(TicketFeedbackEntity $ticketFeedback)
    {
        $this->message     = $ticketFeedback->getMessage();
        $this->rating      = $ticketFeedback->getRating();
        $this->ratingType  = $ticketFeedback->getRatingType();
        $this->dateCreated = $ticketFeedback->getDateCreated();
        $this->person      = $ticketFeedback->getPerson();
    }
}
