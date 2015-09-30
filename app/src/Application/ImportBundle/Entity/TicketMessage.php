<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Entity;

use DateTime;
use Exception;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting ticket message entity.
 *
 * Class TicketMessage
 */
final class TicketMessage extends AbstractEntity implements PersonAwareInterface, AttachmentsAwareInterface
{
    /**
     * @var Ticket
     */
    private $ticket;

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
     * @var string
     */
    private $message_html;

    /**
     * @var bool
     */
    private $is_note = false;

    /**
     * @var Collection
     */
    private $attachments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attachments = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_TICKET_MESSAGE;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOid()
    {
        return ($this->ticket ? $this->ticket->getOid().'-' : '').parent::getOid();
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;

        return $this;
    }

    /**
     * Returns date created.
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date created.
     *
     * @param DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * Returns text message content.
     *
     * @return string
     */
    public function getMessageText()
    {
        return $this->message_text;
    }

    /**
     * Set text message content.
     *
     * @param string $message_text
     *
     * @return $this
     */
    public function setMessageText($message_text)
    {
        $this->message_text = $message_text;

        return $this;
    }

    /**
     * Returns html message content.
     *
     * @return string
     */
    public function getMessageHtml()
    {
        return $this->message_html;
    }

    /**
     * Set html message content.
     *
     * @param string $message_html
     *
     * @return $this
     */
    public function setMessageHtml($message_html)
    {
        $this->message_html = $message_html;

        return $this;
    }

    /**
     * Does the ticket message have content?
     *
     * @return bool
     */
    public function hasMessageContent()
    {
        return $this->message_text || $this->message_html;
    }

    /**
     * Is the ticket message a note?
     *
     * @return bool
     */
    public function isNote()
    {
        return $this->is_note;
    }

    /**
     * Set as note.
     *
     * @param bool $is_note
     *
     * @return $this
     */
    public function setAsNote($is_note)
    {
        $this->is_note = (bool) $is_note;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->attach($attachment);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (!$this->date_created) {
            throw new Exception('Date created is not set up');
        }

        return array(
            'oid'          => $this->oid,
            'person'       => $this->person_email,
            'date_created' => $this->date_created->format('Y-m-d H:i:s'),
            'message_text' => $this->message_text,
            'message_html' => $this->message_html,
            'is_note'      => $this->is_note,
            'attachments'  => $this->attachments->entitiesToArray(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('person_email', new Constraints\NotBlank())
            ->addPropertyConstraint('person_email', new Constraints\Email())

            ->addPropertyConstraint('date_created', new Constraints\NotBlank())
            ->addPropertyConstraint('date_created', new Constraints\DateTime())

            ->addGetterConstraint('messageContent', new Constraints\True())
            ->addPropertyConstraint('attachments', new Constraints\Valid())
        ;
    }
}
