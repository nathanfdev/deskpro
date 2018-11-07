<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Feedback left on tickets.
 */
class TicketFeedback extends \Application\DeskPRO\Domain\DomainObject
{
    const RATE_NEGATIVE = -1;
    const RATE_NEUTRAL  = 0;
    const RATE_POSITIVE = 1;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket = null;

    /**
     * @var \Application\DeskPRO\Entity\TicketMessage
     */
    protected $ticket_message = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var string
     */
    protected $rating = 0;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var bool
     */
    protected $_is_new = false;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->_is_new = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Is this is a new record? (ie not persisted, or persisted this request).
     *
     * @return bool
     */
    public function isNewFeedback()
    {
        return $this->_is_new;
    }

    public function getPersonId()
    {
        return $this->person['id'];
    }

    public function setPersonId($id)
    {
        $person         = App::getOrm()->getRepository('DeskPRO:Person')->find($id);
        $this['person'] = $person;
    }

    public function getTicketId()
    {
        return $this->ticket['id'];
    }

    public function setTicketId($id)
    {
        $ticket         = App::getOrm()->getRepository('DeskPRO:Ticket')->find($id);
        $this['ticket'] = $ticket;
    }

    public function getMessageId()
    {
        return $this->ticket_message->id;
    }

    public function setRating($rating)
    {
        if ($rating < -1 || $rating > 1) {
            $rating = 0;
        }

        $this->setModelField('rating', $rating);
    }

    public function ratePositive()
    {
        $this->setRating(1);
    }

    public function setMessage($message)
    {
        $this->setModelField('message', $message);
    }

    public function rateNegative()
    {
        $this->setRating(-1);
    }

    public function rateNeutral()
    {
        $this->setRating(0);
    }

    public function getRatingType()
    {
        if ($this->rating == 1) {
            return 'positive';
        } elseif ($this->rating == -1) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketFeedback';
        $metadata->setPrimaryTable(['name' => 'ticket_feedback']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'rating',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'rating',
        ]);
        $metadata->mapField([
            'fieldName'  => 'message',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'message',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket_message',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'message_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
