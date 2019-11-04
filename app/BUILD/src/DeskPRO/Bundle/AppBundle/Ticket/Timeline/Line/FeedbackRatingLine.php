<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

use Application\DeskPRO\Entity\TicketFeedback;

class FeedbackRatingLine implements LineInterface
{
    /**
     * @var TicketFeedback
     */
    private $feedback;

    /**
     * @param TicketFeedback $feedback
     */
    public function __construct(TicketFeedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @return TicketFeedback
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'ticket_feedback';
    }

    public function getPerson()
    {
        return $this->feedback->getPerson();
    }

    public function getDateTime()
    {
        return $this->feedback->getDateCreated();
    }
}
