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

use \DeskPRO\App;
use \DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\CoreBundle\Entity\UsergroupPropertyPermission;
use \Application\CoreBundle\Entity\PersonFieldDada;


/**
 * An organization is a grouping we put similar people into (eg companies).
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="organizations")
 */
class Organization extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The organization name
	 *
	 * @var string
	 * @Column(name="name", type="text")
	 */
	protected $name = null;
}
