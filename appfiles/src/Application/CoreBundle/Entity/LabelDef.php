<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * These are pre-defined labels that are allowed to be used.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="label_defs",
 *     indexes={@orm:Index(name="object_type_idx", columns={"object_type"})}
 * )
 */
class LabelDefinition extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * The object type the definition is aplied to (should be a table name).
	 * 
	 * @var string
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="object_type", type="string", length=80)
	 */
	protected $object_type;
}