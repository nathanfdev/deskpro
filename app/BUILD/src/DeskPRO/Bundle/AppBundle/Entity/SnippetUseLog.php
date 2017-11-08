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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;
use DateTime;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations\Property;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SnippetUseLog.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\SnippetUseLogRepository")
 * @ORM\Table(name="snippet_use_log", indexes={@ORM\Index(name="date_created", columns={"date_created"})})
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class SnippetUseLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var DateTime
     */
    protected $dateCreated;

    /**
     * Ticket Message where the snippet was used.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\TicketMessage", cascade={"persist"})
     * @ORM\JoinColumn(name="ticket_message_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketMessage>")
     *
     * @var TicketMessage
     */
    protected $ticketMessage;

    /**
     * Chat Message where the snippet was used.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\ChatMessage", cascade={"persist"})
     * @ORM\JoinColumn(name="chat_message_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\ChatMessage>")
     *
     * @var ChatMessage
     */
    protected $chatMessage;

    /**
     * Snippet used.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Snippet")
     * @ORM\JoinColumn(name="snippet_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Snippet>")
     *
     * @Assert\NotBlank()
     *
     * @var Snippet
     */
    protected $snippet;

    /**
     * Snippet translated.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Language")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @Assert\NotBlank()
     *
     * @var Language
     */
    protected $language;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @Assert\NotBlank()
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups({"list"})
     *
     * @var int
     */
    protected $rating = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $type;

    /**
     * Property manually set to containt ticket feedback message.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $message = '';

    public static function createSnippetTicketLog(TicketMessage $ticketMessage, Person $person, SnippetTranslation $snippetTranslation)
    {
        $log = new self();
        $log->setSnippet($snippetTranslation->getSnippet());
        $log->setLanguage($snippetTranslation->getLanguage());
        $log->setTicketMessage($ticketMessage);
        $log->setPerson($person);
        $log->setType('ticket');
        $log->setDateCreated(new DateTime());

        return $log;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return SnippetUseLog
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param DateTime $dateCreated
     *
     * @return SnippetUseLog
     */
    public function setDateCreated($dateCreated)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return TicketMessage
     */
    public function getTicketMessage()
    {
        return $this->ticketMessage;
    }

    /**
     * @param TicketMessage $ticketMessage
     *
     * @return SnippetUseLog
     */
    public function setTicketMessage($ticketMessage)
    {
        $this->setModelField('ticketMessage', $ticketMessage);

        return $this;
    }

    /**
     * @return ChatMessage
     */
    public function getChatMessage()
    {
        return $this->chatMessage;
    }

    /**
     * @param ChatMessage $chatMessage
     *
     * @return SnippetUseLog
     */
    public function setChatMessage($chatMessage)
    {
        $this->setModelField('chatMessage', $chatMessage);

        return $this;
    }

    /**
     * @return Snippet
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @param Snippet $snippet
     *
     * @return SnippetUseLog
     */
    public function setSnippet($snippet)
    {
        $this->setModelField('snippet', $snippet);

        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     *
     * @return SnippetUseLog
     */
    public function setLanguage($language)
    {
        $this->setModelField('language', $language);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return SnippetUseLog
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param mixed $rating
     *
     * @return SnippetUseLog
     */
    public function setRating($rating)
    {
        $this->setModelField('rating', $rating);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return SnippetUseLog
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return SnippetUseLog
     */
    public function setMessage($message)
    {
        $this->setModelField('message', $message);

        return $this;
    }
}
