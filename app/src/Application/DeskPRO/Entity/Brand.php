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
 * @subpackage
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Application\DeskPRO\Entity\Blob;

/**
 * @property int $id
 * @property string $name
 * @property Blob $logo_blob
 */
class Brand extends DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string the brand name
	 */
	protected $name;

	/**
	 * @var Blob
	 */
	protected $logo_blob;


	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['logo_blob'] = $this->logo_blob ? $this->logo_blob->toApiData() : null;

		return $data;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$builder = new ClassMetadataBuilder($metadata);
		$builder->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\Brand');
		$builder->setChangeTrackingPolicyNotify();
		$builder->setTable('brands');

		$builder->mapId();
		$builder->mapString('name');
		$builder->addOwningOneToOne('logo_blob', 'Application\DeskPRO\Entity\Blob');
	}
}
