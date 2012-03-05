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

/**
 * Base class used for storing custom field data.
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class CustomDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 * The form field this is attached to
	 *
	 * @var \Application\DeskPRO\Entity\CustomDefXXX
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefXXX")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id")
	 */
	//protected $field = null;

	/**
	 * IMPLEMENT IN CHILD CLASS
	 *
	 * @var \Application\DeskPRO\Entity\Xxx
	 * @ORM_Mapping\ManyToOne(targetEntity="xxx")
	 * @ORM_Mapping\JoinColumn(name="xxx_id", referencedColumnName="id")
	 */
	//protected $xxx;

	/**
	 * User numeric data
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="value", type="integer")
	 */
	protected $value = 0;

	/**
	 * User string data
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="input", type="text")
	 */
	protected $input = '';



	/**
	 * Get the value or input.
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->value ? $this->value : $this->input;
	}


	public function getFieldId()
	{
		return $this->field['id'];
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->isMappedSuperclass = true; 
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->setPrimaryTable(array( 'name' => 'CustomDataAbstract', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
	}
}

