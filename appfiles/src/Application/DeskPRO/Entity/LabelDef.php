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

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * These are pre-defined labels that are allowed to be used.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="label_defs")
 * )
 */
class LabelDef extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="label", type="string", length=255)
	 */
	protected $label;
}