<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter;

interface LinkConfigRepoInterface
{
    /**
     * Returns an array like:
     * [
     *   'route' => 'route_name',
     *   'route_param_map' => ['param' => 'value']
     * ].
     *
     * @param object $object  the entity/object itself
     * @param string $context the area: "portal", "agent"
     * @param string $type    a specifier, since multiple routes can be configured
     *
     * @return array|string an array with the format above or the string "custom"
     */
    public function getRouteInfo($object, $context, $type);
}
