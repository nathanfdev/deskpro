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

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting ticket message entity.
 *
 * Class TicketMessage
 */
class TicketMessage implements PersonAwareInterface, AttachmentsAwareInterface, OidAwareModelInterface
{
    use OidRequiredAwareModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_created;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"html", "text"})
     */
    private $format = 'html';

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_note = false;

    /**
     * @var Attachment[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Attachment>")
     *
     * @Assert\Valid()
     */
    private $attachments = [];

    /**
     * {@inheritdoc}
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Returns date created.
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date created.
     *
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * Returns text message content.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set text message content.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns html message content.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set html message content.
     *
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
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
        $this->attachments[] = $attachment;

        return $this;
    }
}
