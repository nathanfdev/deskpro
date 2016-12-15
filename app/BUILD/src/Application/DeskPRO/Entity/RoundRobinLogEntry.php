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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class RoundRobinLogEntry extends DomainObject
{
    /** @var int */
    protected $id;
    /** @var RoundRobin */
    protected $rr;
    /** @var int */
    protected $ticketId;
    /** @var string */
    protected $ticketSubject;
    /** @var array */
    protected $actions;
    /** @var \DateTime */
    protected $created;

    protected $translate;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->actions = array();
    }

    public function addActionNoOnline()
    {
        $this->actions[] = array(
            'phrase' => 'adm.round_robins.log_no_agents_online',
            'params' => array(),
        );
    }

    public function addActionAssigned(Person $person)
    {
        $this->actions[] = array(
            'phrase' => 'adm.round_robins.log_assigned',
            'params' => array('name' => $person->getDisplayName()),
        );
    }

    public function addActionSkippedOffline(Person $person)
    {
        $this->actions[] = array(
            'phrase' => 'adm.round_robins.log_skipped_offline',
            'params' => array('name' => $person->getDisplayName()),
        );
    }

    public function addActionSkippedDisabled(Person $person)
    {
        $this->actions[] = array(
            'phrase' => 'adm.round_robins.log_skipped_disabled',
            'params' => array('name' => $person->getDisplayName()),
        );
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setPrimaryTable(array('name' => 'round_robin_log'));

        $metadata->mapField(array(
            'fieldName'  => 'id',
            'columnName' => 'id',
            'type'       => 'integer',
            'id'         => true,
        ));

        $metadata->mapField(array(
            'fieldName'  => 'ticketId',
            'columnName' => 'ticket_id',
            'type'       => 'integer',
        ));

        $metadata->mapField(array(
            'fieldName'  => 'ticketSubject',
            'columnName' => 'ticket_subject',
        ));

        $metadata->mapField(array(
            'fieldName'  => 'actions',
            'columnName' => 'actions',
            'type'       => 'array',
        ));

        $metadata->mapField(array(
            'fieldName'  => 'created',
            'columnName' => 'created',
            'type'       => 'datetime',
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'rr',
            'targetEntity' => 'Application\DeskPRO\Entity\RoundRobin',
            'joinColumns'  => array(array(
                'onDelete' => 'cascade',
            )),
        ));
    }
}
