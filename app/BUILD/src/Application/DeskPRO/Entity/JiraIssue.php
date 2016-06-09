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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * The JiraIssue Class
 * Emulates a Jira Issue.
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class JiraIssue extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The associated DeskPRO ticket.
     *
     * @var \Application\DeskPRO\Entity\Ticket Associated ticket
     */
    protected $ticket;

    /**
     * @var int JIRA issue id
     */
    protected $issue_id;

    /**
     * @var int JIRA issue status id
     */
    protected $status_id;

    /**
     * Export time.
     *
     * @var int Timestamp
     */
    protected $created;

    public function __construct()
    {
        $this->created = new \DateTime();
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\JiraIssue';
        $metadata->setPrimaryTable(['name' => 'jira_issues']);

        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'issue_id',
            'type'       => 'integer',
            'columnName' => 'issue_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'status_id',
            'type'       => 'integer',
            'columnName' => 'status_id',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'created',
            'type'       => 'datetime',
            'columnName' => 'created',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\DeskPRO\Entity\Ticket',
            'joinColumns'  => [
                [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'cascade',
                ],
            ],
            'dpApi' => true,
        ]);
    }
}
