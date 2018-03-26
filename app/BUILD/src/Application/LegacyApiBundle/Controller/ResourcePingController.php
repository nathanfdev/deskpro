<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * A remote resource pings this script to notify the system that some record was updated.
 *
 * The resource and record are added to a worker queue, and then it will be processed
 * later (hopefully in a few seconds).
 *
 * @ApiModes("all")
 */
class ResourcePingController extends AbstractController
{
    /**
     * A remote site will ping us when one of their objects has been updated.
     *
     * @param int   $resource_id
     * @param mixed $record_id
     */
    public function postObjectUpdated($resource_id, $record_id)
    {
        $filter = $this['deskpro.core.filter_factory']->createForFilter('object_updated');
        $filter->send("$resource_id:$record_id");

        return $this->createApiResponse(['success' => true]);
    }
}
