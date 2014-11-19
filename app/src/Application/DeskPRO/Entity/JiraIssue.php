<?php

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * The JiraIssue Class
 * Emulates a Jira Issue
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class JiraIssue extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     *
     */
    protected $id = null;

    /**
     * The associated DeskPRO ticket
     *
     * @var \Application\DeskPRO\Entity\Ticket Associated ticket
     */
    protected $ticket;

    /**
     * The associated JIRA issue
     *
     * @var \Jira\Entity\Issue Associate JIRA issue
     */
    protected $issue;

    /**
     * Export time
     *
     * @var int Timestamp
     */
    protected $created;

    /**
     * When was it last synced with JIRA?
     * @var int
     */
    protected $lastSynced = 0;

    /**
     * Issues Comments
     * @var
     */
    protected $comments;

    public function __construct()
    {
        $this->lastSynced = $this->created = time();
    }

    public function addComment(JiraIssueComment $comment)
    {
        $this->comments->add($comment);

        $comment->issue	= $this;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);

        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\JiraIssue';

        $metadata->setPrimaryTable(array(
            'name' => 'jira_issues',
            'indexes' => array(
                'issue_id_idx' => array( 'columns' => array( 0 => 'issue_id', ), ),
            )
        ));

        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
        $metadata->mapField(array( 'fieldName' => 'issue', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'issue_id', ));
        $metadata->mapField(array( 'fieldName' => 'created', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'created', ));
        $metadata->mapField(array( 'fieldName' => 'lastSynced', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'last_synced', ));

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        //$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'dpApi' => true ));
        $metadata->mapManyToOne(array( 'fieldName' => 'ticket', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'ticket_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'dpApi' => true ));
        $metadata->mapOneToMany(array( 'fieldName' => 'comments', 'targetEntity' => 'Application\\DeskPRO\\Entity\\JiraIssueComment', 'cascade' => array( 0 => 'remove', 1 => 'persist', 3 => 'merge', ), 'mappedBy' => 'issue'));
    }
}
