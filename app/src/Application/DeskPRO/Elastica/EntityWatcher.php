<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

/**
 * Watches database transactions and keeps search index up to date
 */
class EntityUpdater implements EventSubscriber
{
	public function postPersist()
	{

	}

	public function postRemove()
	{

	}

	public function postUpdate()
	{

	}

	public function getSubscribedEvents()
	{
		return array(
			Events::postPersist,
			Events::postRemove,
			Events::postUpdate,
		);
	}
}