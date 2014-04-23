<?php

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * The JiraIssueComment Class
 * Emulates a Jira Issue Comment
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class JiraIssueComment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 *
	 */
	protected $id = null;
	
	/**
	 * Comment ID on JIRA
	 * 
	 * @var int
	 */
	protected $jiraId;

	/**
	 * The associated JIRAIssue
	 * 
	 * @var \Application\DeskPRO\Entity\JiraIssue Associated JIRAIssue
	 */
	protected $issue;
	
	/**
	 * The Associated TicketMessage
	 * 
	 * @var \Application\DeskPRO\Entity\TicketMessage The Associated TicketMessage
	 */
	protected $ticketMessage;
	
	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\JiraIssueComment';
		
		$metadata->setPrimaryTable(array(
			'name' => 'jira_issue_comments',
			'indexes' => array(
				'issue_id_idx' => array( 'columns' => array( 0 => 'issue_id', ), ),
			)
		));
		
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'jiraId', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'jira_comment_id', ));
		
		$metadata->mapManyToOne(array( 'fieldName' => 'issue', 'targetEntity' => 'Application\\DeskPRO\\Entity\\JiraIssue', 'inversedBy' => 'comments', 'joinColumns' => array( 0 => array( 'name' => 'issue_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'dpApi' => true ));
		$metadata->mapOneToOne(array( 'fieldName'  => 'ticketMessage',  'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage', 'joinColumns'  => array(array( 'name' => 'message_id', 'referencedColumnName' => 'id',	'nullable' => true,	'onDelete' => 'CASCADE', 'fetch' => 'EAGER'))));
	}
}