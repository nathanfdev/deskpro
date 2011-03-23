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

use Application\DeskPRO\App;

/**
 * Base labels associations class
 *
 * @orm:HasLifecycleCallbacks
 * @orm:MappedSuperclass
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
	 * @orm:Id
	 * @orm:Column(name="label", type="string", length=255)
	 */
	protected $label;



	/**
	 * After a new association is made, we need to make sure the def table has this
	 * record.
	 * 
	 * @orm:PostPersist
	 */
	public function syncWithDef()
	{
		App::getDb()->executeUpdate("INSERT IGNORE INTO label_defs SET label_type = ?, label = ?", array(
			static::LABEL_TYPENAME,
			$this->label
		));
	}
}