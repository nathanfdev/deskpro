<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Labels;

use \Symfony\Component\DependencyInjection\Container;

/**
 * We're subclassing the EntityManager because it's a good place to stick an entity factory for
 * when we need to create new instances of some entity, and a good place to pass in the container.
 *
 * TODO: This is no longer needed. Entities can use Application\DeskPRO\App to fetch required objects.
 * We should just start creating entities normally again and get rid of this.
 */
interface LableReadyInterface
{
	public function removeLabel($label);
	public function addLabel($label);
}