<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ticket attachments.
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketAttachment extends DomainObject
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Ticket this attachment belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * Who created the attachment.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * Actual attachment.
     *
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @Assert\Valid()
     *
     * @var Blob
     */
    protected $blob;

    /**
     * Message - holder of this attachment.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketMessage>")
     *
     * @Assert\NotBlank()
     *
     * @var TicketMessage
     */
    protected $message;

    /**
     * True if this attachmen just a note.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_agent_note = false;

    /**
     * Is this attachment is embed in message.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_inline = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set person.
     *
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * Set blob entity.
     *
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob(Blob $blob = null)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }

    /**
     * @param $message
     */
    public function setMessage($message)
    {
        $this->setModelField('message', $message);

        // Automatically set the is_agent_note field
        if ($message && $message->is_agent_note) {
            $this->is_agent_note = true;
        }
    }

    /**
     * @param bool $isInline
     *
     * @return $this
     */
    public function setIsInline($isInline)
    {
        $this->setModelField('is_inline', $isInline);

        return $this;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \RuntimeException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        if (!$this->message) {
            throw new \RuntimeException('Unable to add attachment, no message is referred to.');
        }
        if (!$this->message->getTicket()) {
            throw new \RuntimeException('Unable to add attachment, message is not added to ticket.');
        }
        if (!$em->contains($this->message) && !$em->contains($this->message->getTicket())) {
            throw new \RuntimeException('Unable to persist attachment, message is not persisted.');
        }

        $this->ticket                           = $this->message->ticket;
        $this->message->ticket->has_attachments = true;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @return bool
     */
    public function isInline()
    {
        return $this->is_inline;
    }

    /**
     * @return bool
     */
    public function isAgentNote()
    {
        return $this->is_agent_note;
    }

    /**
     * @return TicketMessage
     */
    public function getMessage()
    {
        return $this->message;
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
    public function setTicket(Ticket $ticket = null)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketAttachment';
        $metadata->setPrimaryTable(['name' => 'tickets_attachments']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->addLifecycleCallback('prePersist', 'prePersist');

        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_agent_note',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_agent_note',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_inline',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_inline',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'mappedBy'     => null,
                'inversedBy'   => 'attachments',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'blob',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'blob_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'message',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'inversedBy'   => 'attachments',
                'joinColumns'  => [
                    [
                        'name'                 => 'message_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                    ],
                ],
            ]
        );
    }
}
