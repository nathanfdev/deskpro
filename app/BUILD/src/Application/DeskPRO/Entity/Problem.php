<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

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
     * @return Person
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param Person $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Problem';

        if (defined('DP_INTERFACE') && DP_INTERFACE != 'install') {
            foreach (array(
                         Events::prePersist,
                         Events::postPersist,
                         Events::preUpdate,
                         Events::postUpdate,
                     ) as $event) {
                $metadata->addEntityListener(
                    $event,
                    'Application\DeskPRO\Entity\EventListener\ProblemListener',
                    'on'.ucfirst($event)
                );
            }
        }

        $metadata->setPrimaryTable(
            array(
                'name'    => 'problems',
                'indexes' => array(),
            )
        );

        $metadata->mapField(
            array(
                'fieldName' => 'id',
                'type'      => 'integer',
                'id'        => true,
            )
        );

        $metadata->mapField(
            array(
                'fieldName' => 'title',
            )
        );

        $metadata->mapField(
            array(
                'fieldName' => 'created',
                'type'      => 'datetime',
            )
        );

        $metadata->mapField(
            array(
                'fieldName' => 'is_open',
                'type'      => 'boolean',
            )
        );

        $metadata->mapManyToOne(
            array(
                'fieldName'    => 'creator',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'joinColumns'  => array(
                    array(
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ),
                ),
                'dpApi' => true,
            )
        );

        $metadata->mapManyToMany(
            array(
                'fieldName'    => 'tickets',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'cascade'      => array('persist', 'merge'),
                'mappedBy'     => 'problems',
                'joinTable'    => array(
                    'name'        => 'problem2tickets',
                    'joinColumns' => array(
                        array(
                            'name'                 => 'problem_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                        ),
                    ),
                    'inverseJoinColumns' => array(
                        array(
                            'name'                 => 'ticket_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                        ),
                    ),
                ),
            )
        );
    }
}
