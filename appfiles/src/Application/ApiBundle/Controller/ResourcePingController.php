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
 * A remote resource pings this script to notify the system that some record was updated.
 *
 * The resource and record are added to a worker queue, and then it will be processed
 * later (hopefully in a few seconds).
 */
class ResourcePingController extends AbstractController
{
	/**
	 * A remote site will ping us when one of their objects has been updated.
	 *
	 * @param int $resource_id
	 * @param mixed $record_id
	 */
	public function postObjectUpdated($resource_id, $record_id)
	{
		$queue = $this['deskpro.core.queue_factory']->createForQueue('object_updated');
		$queue->send("$resource_id:$record_id");

		return $this->createApiResponse(array('success' => true));
	}
}