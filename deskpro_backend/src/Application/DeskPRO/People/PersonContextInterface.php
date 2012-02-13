<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;

/**
 * This interface is meant to signify that the object should run in the context
 * of a person. This usually means some type of permissions apply.
 */
interface PersonContextInterface
{
	public function setPersonContext(Person $person);
}
