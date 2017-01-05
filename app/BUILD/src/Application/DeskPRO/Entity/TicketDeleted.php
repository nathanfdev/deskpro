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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A log of deleted tickets.
 */
class TicketDeleted extends DomainObject
{
    /**
     * @var int
     */
    protected $ticket_id;

    /**
     * @var string
     */
    protected $old_ptac;

    /**
     * @var string
     */
    protected $old_ref = '';

    /**
     * @var int
     */
    protected $new_ticket_id = 0;

    /**
     * @var int
     * @var Person
     */
    protected $by_person;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var string
     */
    protected $reason;

    /**
     * TicketDeleted constructor.
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
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticket_id;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return int
     */
    public function getByPersonId()
    {
        if ($this->by_person) {
            return $this->by_person['id'];
        }

        return 0;
    }

    /**
     * @param $id
     */
    public function setByPersonId($id)
    {
        if ($id) {
            $this['by_person'] = App::getEntityRepository('DeskPRO:Person')->find($id);
        } else {
            $this['by_person'] = null;
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable([
            'name'    => 'tickets_deleted',
            'indexes' => [
                'old_ref_idx' => ['columns' => ['old_ref']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'ticket_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ticket_id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'old_ptac',
            'type'       => 'string',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'old_ptac',
        ]);
        $metadata->mapField([
            'fieldName'  => 'old_ref',
            'type'       => 'string',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'old_ref',
        ]);
        $metadata->mapField([
            'fieldName'  => 'new_ticket_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'new_ticket_id',
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
            'fieldName'  => 'reason',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'reason',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'by_person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'by_person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
