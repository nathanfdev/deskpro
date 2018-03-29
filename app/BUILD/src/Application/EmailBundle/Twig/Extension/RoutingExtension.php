<?php

/**
 * DeskPRO.
 *
 * @category Templating
 */

namespace Application\EmailBundle\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;

class RoutingExtension extends BaseRoutingExtension
{
    /**
     * Custom getPath to eat exception when not in debug mode.
     *
     * This is because people can screw up their site if they edit templates and then try to render
     * a malformed link. In that scenario, better to not fatal error.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $relative
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getPath($name, $parameters = [], $relative = false)
    {
        try {
            return parent::getPath($name, $parameters, $relative);
        } catch (\Exception $e) {
            /* @var \DpRun\DpEnv $DP_ENV */
            global $DP_ENV;
            if ($DP_ENV->isDebug()) {
                throw $e;
            }

            return '';
        }
    }
}
