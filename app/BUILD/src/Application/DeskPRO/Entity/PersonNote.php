<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A note is a private note added by an agent to a persons account.
 *
 * @JMS\ExclusionPolicy("all")
 */
class PersonNote extends DomainObject
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
     * The person the note is attached to.
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * The agent that added the note.
     *
     * @Assert\NotNull()
     * @AppAssert\Person\PersonType(type="agent")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $agent;

    /**
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The note contents.
     *
     * @Assert\NotBlank()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $note;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return $this
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function setAgent($agent)
    {
        $this->setModelField('agent', $agent);

        return $this;
    }

    /**
     * @return string
     */
    public function getNoteHtml()
    {
        return nl2br(htmlspecialchars($this->note), true);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonNote';
        $metadata->setPrimaryTable(['name' => 'people_notes']);
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
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'note',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'note',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'notes',
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'agent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'agent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
    }
}
