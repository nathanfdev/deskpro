<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

/**
 * Base labels associations class
 *
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\MappedSuperclass
 */
class LabelAssocAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The 'type' of label this is for, as it could be found in the
	 * LabelDef.
	 */
	const LABEL_TYPENAME = 'OVERRIDE';

	/**
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="label", type="string", length=255)
	 */
	protected $label;



	/**
	 * After a new association is made, we need to make sure the def table has this
	 * record.
	 * 
	 * @ORM_Mapping\PostPersist
	 */
	public function syncWithDef()
	{
		App::getDb()->executeUpdate("INSERT IGNORE INTO label_defs SET label_type = ?, label = ?", array(
			static::LABEL_TYPENAME,
			$this->label
		));
	}


	public function __toString()
	{
		return $this->label;
	}
}