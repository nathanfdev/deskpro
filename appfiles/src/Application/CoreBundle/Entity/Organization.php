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

use \Application\DeskPRO\App;
use \Application\DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\CoreBundle\Entity\UsergroupPropertyPermission;
use \Application\CoreBundle\Entity\PersonFieldDada;


/**
 * An organization is a grouping we put similar people into (eg companies).
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="organizations")
 */
class Organization extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The organization name
	 *
	 * @var string
	 * @orm:Column(name="name", type="text")
	 */
	protected $name = null;
}
