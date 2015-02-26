<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TicketMessageEmailId extends \Application\DeskPRO\Domain\DomainObject
{
	protected $id;

	protected $message;

	protected $email_id;

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
//		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketMessageEmailId';
		$metadata->setPrimaryTable(array(
			'name' => 'tickets_message_email_id',
			'indexes' => array(
				'email_id_idx' => array('columns' => array('email_id')),
			)
		));

		$metadata->mapField(array(
			'fieldName' => 'id',
			'id' => true,
			'type' => 'integer',
		));

		$metadata->mapField(array(
			'fieldName' => 'email_id',
			'nullable' => false,
			'columnName' => 'email_id',
		));

		$metadata->mapManyToOne(array(
			'fieldName' => 'message',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
			'inversedBy' => 'email_message_id',
			'joinColumns' => array(array(
				'name' => 'message_id',
				'referencedColumnName' => 'id',
				'onDelete' => 'CASCADE',
			)),
		));
	}
}
