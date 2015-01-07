<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

use DateTime;

/**
 * Exported ticket message entity
 *
 * Class TicketMessage
 * @package Application\ImportBundle\Entity
 */
class TicketMessage implements ToArrayInterface
{
    /**
     * @var string
     */
    private $person_email;

    /**
     * @var DateTime
     */
    private $date_created;

    /**
     * @var string
     */
    private $message_text;

    /**
     * @var array
     */
    private $attachments = array();

    /**
     * @return string
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * @param string $person_email
     * @return $this
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageText()
    {
        return $this->message_text;
    }

    /**
     * @param string $message_text
     * @return $this
     */
    public function setMessageText($message_text)
    {
        $this->message_text = $message_text;
        return $this;
    }

    /**
     * Add a message attachment
     *
     * @param TicketMessageAttachment $attachment
     * @return $this
     */
    public function addAttachment(TicketMessageAttachment $attachment)
    {
        $this->attachments = $attachment;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $attachments = array();
        foreach ($this->attachments as $attachment) {
            /** @var TicketMessageAttachment $attachment */
            $attachments[] = $attachment->toArray();
        }

        return array(
            'person'       => $this->person_email,
            'date_created' => $this->date_created->format('Y-m-d H:i:s'),
            'message_text' => $this->message_text,
            'attachments'  => $attachments,
        );
    }
}
