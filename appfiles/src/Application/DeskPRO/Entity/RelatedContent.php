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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * Related content
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\RelatedContent")
 * @orm:Table(name="related_content")
 */
class RelatedContent extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @orm:Column(name="object_type", type="string", length=100)
	 * @orm:Id
	 */
	protected $object_type;

	/**
	 * @var int
	 * @orm:Column(name="object_id", type="integer")
	 * @orm:Id
	 */
	protected $object_id = null;

	/**
	 * @var string
	 * @orm:Column(name="rel_object_type", type="string", length=100)
	 * @orm:Id
	 */
	protected $rel_object_type;

	/**
	 * @var int
	 * @orm:Column(name="rel_object_id", type="integer")
	 * @orm:Id
	 */
	protected $rel_object_id = null;

	public function setRelation($entity1, $entity2)
	{
		$this->object_type      = $entity1->getTableName();
		$this->object_id        = $entity1->getId();

		$this->rel_object_type  = $entity2->getTableName();
		$this->rel_object_id    = $entity2->getId();
	}
}