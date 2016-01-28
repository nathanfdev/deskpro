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

namespace Application\DeskPRO\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class Problem extends \Application\DeskPRO\Domain\DomainObject
{
    const FILTER_PREFIX = 'problem_';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var Person
     */
    protected $creator;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var bool
     */
    protected $is_open;

    /**
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
