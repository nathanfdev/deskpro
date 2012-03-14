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

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\Entity;

/**
 * Profile-related display information
 *
 */
class PersonDisplayElement extends \Application\DeskPRO\Domain\DomainObject
{
	const ZONE_AGENT = 'agent';
	const ZONE_USER  = 'user';

	const ELEMENT_FIELD = 'field';
	const ELEMENT_WIDGET = 'widget';

	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * Where this display field description applies: user, agent
	 *
	 * @var string
	 */
	protected $display_zone;

	/**
	 * The type of elemenet: ticket_field, widget
	 *
	 * @var string
	 */
	protected $element_type;

	/**
	 * The ID of the element
	 *
	 * @var string
	 */
	protected $element_id = 0;

	/**
	 * The initial state of the element. When the conditions fail,
	 * this state is reversed.
	 *
	 * @var string
	 */
	protected $initial_state = 'visible';

	/**
	 * An array of checks to run to see if the element should display.
	 * All of these must match.
	 *
	 * @var array
	 */
	protected $conds_all = array();

	/**
	 * An array of checks to run to see if the element should display.
	 * Any one of these must match.
	 *
	 * @var array
	 */
	protected $conds_any = array();

	/**
	 * @var int
	 */
	protected $display_order = 0;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getDepartmentId()
	{
		if ($this->department) {
			return $this->department['id'];
		}

		return 0;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setPrimaryTable(array( 'name' => 'person_display_elements', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'display_zone', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'display_zone', ));
		$metadata->mapField(array( 'fieldName' => 'element_type', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'element_type', ));
		$metadata->mapField(array( 'fieldName' => 'element_id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'element_id', ));
		$metadata->mapField(array( 'fieldName' => 'initial_state', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'initial_state', ));
		$metadata->mapField(array( 'fieldName' => 'conds_all', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'conds_all', ));
		$metadata->mapField(array( 'fieldName' => 'conds_any', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'conds_any', ));
		$metadata->mapField(array( 'fieldName' => 'display_order', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'display_order', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
