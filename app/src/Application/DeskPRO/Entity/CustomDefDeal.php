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
 * A custom field definition
 *
 */
class CustomDefDeal extends CustomDefAbstract
{
	/**
	 * @var CustomDefDeal
	 */
	protected $parent = null;

	/**
	 * Field children
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $children = null;



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomDefDeal'; 
		$metadata->setPrimaryTable(array( 'name' => 'custom_def_deal', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'js_class', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'js_class', )); 
		$metadata->mapField(array( 'fieldName' => 'has_form_template', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'has_form_template', )); 
		$metadata->mapField(array( 'fieldName' => 'has_display_template', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'has_display_template', )); 
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'title', )); 
		$metadata->mapField(array( 'fieldName' => 'handler_class', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'handler_class', )); 
		$metadata->mapField(array( 'fieldName' => 'options', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'options', )); 
		$metadata->mapField(array( 'fieldName' => 'is_user_enabled', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_user_enabled', )); 
		$metadata->mapField(array( 'fieldName' => 'is_enabled', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_enabled', )); 
		$metadata->mapField(array( 'fieldName' => 'display_order', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'display_order', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'parent', 'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefDeal', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'children', 'joinColumns' => array( 0 => array( 'name' => 'parent_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'children', 'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefDeal', 'cascade' => array( 0 => 'remove', 1 => 'persist', 3 => 'merge', ), 'mappedBy' => 'parent', 'orphanRemoval' => false, 'orderBy' => array( 'display_order' => 'ASC', ), )); 
		$metadata->mapOneToOne(array( 'fieldName' => 'plugin', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Plugin', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'plugin_id', 'referencedColumnName' => 'id', 'unique' => true, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}
