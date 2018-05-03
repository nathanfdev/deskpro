<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Problem.
 *
 * @JMS\ExclusionPolicy("all")
 */
class Problem extends DomainObject
{
    const FILTER_PREFIX = 'problem_';

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
     * Problem title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title;

    /**
     * Person who created the problem.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $creator;

    /**
     * Date when the problem was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * Is problem still has no solution?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_open;

    /**
     * Tickets associated with problem.
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Ticket>>")
     *
     * @var ArrayCollection
     */
    protected $tickets;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->is_open = true;
        $this->tickets = new ArrayCollection();
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
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param Person $creator
     *
     * @return $this
     */
    public function setCreator($creator)
    {
        $this->setModelField('creator', $creator);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
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
     * @return bool
     */
    public function isOpen()
    {
        return $this->is_open;
    }

    /**
     * @param bool $is_open
     *
     * @return $this
     */
    public function setIsOpen($is_open)
    {
        $this->setModelField('is_open', $is_open);

        return $this;
    }

    /**
     * @return ArrayCollection|Ticket[]
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return $this
     */
    public function setCreated(\DateTime $created)
    {
        $this->setModelField('created', $created);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Problem';

        if (defined('DP_INTERFACE') && DP_INTERFACE != 'install') {
            foreach ([
                         Events::prePersist,
                         Events::postPersist,
                         Events::preUpdate,
                         Events::postUpdate,
                     ] as $event) {
                $metadata->addEntityListener(
                    $event,
                    'Application\DeskPRO\Entity\EventListener\ProblemListener',
                    'on'.ucfirst($event)
                );
            }
        }

        $metadata->setPrimaryTable(
            [
                'name'    => 'problems',
                'indexes' => [],
            ]
        );

        $metadata->mapField(
            [
                'fieldName' => 'id',
                'type'      => 'integer',
                'id'        => true,
            ]
        );

        $metadata->mapField(
            [
                'fieldName' => 'title',
            ]
        );

        $metadata->mapField(
            [
                'fieldName' => 'created',
                'type'      => 'datetime',
            ]
        );

        $metadata->mapField(
            [
                'fieldName' => 'is_open',
                'type'      => 'boolean',
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'creator',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'joinColumns'  => [
                    [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );

        $metadata->mapManyToMany(
            [
                'fieldName'    => 'tickets',
                'targetEntity' => Ticket::class,
                'cascade'      => ['persist', 'merge'],
                'mappedBy'     => 'problems',
                'joinTable'    => [
                    'name'        => 'problem2tickets',
                    'joinColumns' => [
                        [
                            'name'                 => 'problem_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                    'inverseJoinColumns' => [
                        [
                            'name'                 => 'ticket_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                        ],
                    ],
                ],
            ]
        );
    }
}
