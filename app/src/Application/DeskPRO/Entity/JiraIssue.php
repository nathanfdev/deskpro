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
	 * @var JIRA issue id
	 */
	protected $issue_id;

	/**
	 * @var JIRA issue status id
	 */
	protected $status_id;
	
	/**
	 * Export time
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
		$metadata->setPrimaryTable(array('name' => 'jira_issues'));

		$metadata->mapField(array('fieldName' => 'id', 'type' => 'integer', 'columnName' => 'id', 'id' => true));
		$metadata->mapField(array('fieldName' => 'issue_id', 'type' => 'string', 'columnName' => 'issue_id'));
		$metadata->mapField(array('fieldName' => 'status_id', 'type' => 'integer', 'columnName' => 'status_id', 'nullable' => true));
		$metadata->mapField(array('fieldName' => 'created', 'type' => 'datetime', 'columnName' => 'created'));
		$metadata->mapManyToOne(array(
			'fieldName' => 'ticket',
			'targetEntity' => 'Application\DeskPRO\Entity\Ticket',
			'joinColumns' => array(array(
				'name' => 'ticket_id',
				'referencedColumnName' => 'id',
				'onDelete' => 'cascade',
			)),
			'dpApi' => true,
		));
	}
}