<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Comments on articles
 *
 */
class ArticleComment extends CommentAbstract
{
	const OBJ_PROP = 'article';

	/**
	 */
	protected $article;



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ArticleComment'; 
		$metadata->setPrimaryTable(array( 'name' => 'article_comments', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'ip_address', 'type' => 'string', 'length' => 30, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'ip_address', )); 
		$metadata->mapField(array( 'fieldName' => 'email', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'email', )); 
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'name', )); 
		$metadata->mapField(array( 'fieldName' => 'content', 'type' => 'text', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'content', )); 
		$metadata->mapField(array( 'fieldName' => 'status', 'type' => 'string', 'length' => 30, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'status', )); 
		$metadata->mapField(array( 'fieldName' => 'validating', 'type' => 'string', 'length' => 35, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'validating', )); 
		$metadata->mapField(array( 'fieldName' => 'is_reviewed', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_reviewed', )); 
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'date_created', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'article', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Article', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'comment', 'joinColumns' => array( 0 => array( 'name' => 'article_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'visitor', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Visitor', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'visitor_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}
