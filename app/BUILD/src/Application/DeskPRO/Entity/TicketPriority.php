<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Ticket priorities.
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketPriority extends DomainObject implements HasPhraseName
{
    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Priority title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * The priority itself.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $priority = 10;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return App::getTranslator()->getPhraseObject($this, 'title');
    }

    /**
     * Set real title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setRealTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * Returns real title.
     *
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->setModelField('priority', (int) $priority);

        return $this;
    }

    /**
     * Returns priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }
        $phrase_name = 'obj_ticketpriority.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        return $this->title;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketPriority';
        $metadata->setPrimaryTable(['name' => 'ticket_priorities']);
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
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'priority',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'priority',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
