<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\ApiBundle\Controller;

/**
 * A misc resource for doing things like testing if the system is up, or fetching
 * statistics etc.
 */
class PingController extends AbstractController
{
	/**
	 * A remote site will ping us when one of their objects has been updated.
	 *
	 * @param int $resource_id
	 * @param mixed $object_id
	 */
	public function postObjectUpdated($resource_id, $object_id)
	{
		$queue = $this['deskpro.core.queue_factory']->createForQueue('object_updated');
		$queue->send("$resource_id:$object_id");
	}
}