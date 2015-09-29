<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Kernel;

use Application\AgentBundle\AgentBundle;
use DeskPRO\Bundle\ApiBundle\ApiBundle;
use DeskPRO\Bundle\AppBundle\AppBundle;
use DeskPRO\Bundle\PortalBundle\PortalBundle;
use Symfony\Component\HttpKernel\Kernel;

abstract class BaseKernel extends Kernel
{
    private $instantied_but_not_used_bundles = array();

    /**
     * We extend the base functionality of getBundle to allow kernels that don't use a bundle to
     * still reference that bundle in config (reference like "@PortalBundle/Resources/config/routing.yml") etc.
     *
     * For the 99% case, we are never going to call this with a bundle that is not
     * being used in the kernel anyway, usually only for generating the container.
     * If a bundle ver does get called here outside of that, consider registering the
     * bundle into the kernel anyway.
     */
    public function getBundle($name, $first = true)
    {
        if (!isset($this->bundleMap[$name])) {

            /*
             * USEFUL FOR CONFIG/NAMESPACES ONLY when a kernel doesn't have the bundle but needs
             * to locate a resource on that other bundle.
             *
             * We have multiple kernels, and sometimes we need the PATH to another bundle
             * for routing and config purposes.  We do NOT want all bundles in every kernel,
             * but if the ApiKernel encounters @PortalBundle/config/routing.yml and we are in the
             * ApiKernel that does not have PortalBundle enabled, it will throw an error.
             *
             * To avoid this, we instante and return the bundle class so it can access the proper
             * path and namespaces for that bundle instead of throwing an error, while at the
             * same time we retain the original Symfony Kernel way of locating resources.
             *
             * It is important that you do NOT add this new bundle to the $this->bundleMap.
             */

            // we are very explicit of what bundles are allowed here
            // the kernels that actually use these bundles won't hit here because
            // that bundle would already be in the $this->bundleMap in the if statement above
            switch ($name) {
                case 'PortalBundle':
                    // ApiKernel does not have PortalBundle
                    // DpKernel does not have PortalBundle
                    return array($this->getUnusedBundle('PortalBundle'));
                case 'ApiBundle':
                    // PortalKernel does not have ApiBundle
                    // DpKernel does not have ApiBundle
                    return array($this->getUnusedBundle('ApiBundle'));
                case 'AppBundle':
                    // DpKernel does not have AppBundle
                    return array($this->getUnusedBundle('AppBundle'));
                case 'AgentBundle':
                    // PortalKernel does not have AgentBundle
                    // ApiKernel does not have AgentBundle
                    return array($this->getUnusedBundle('AgentBundle'));
                default:
                    break;
            }
        }

        return parent::getBundle($name, $first);
    }

    /**
     * This just makes sure we only instante each bundle once, for a very very small performance gain.
     *
     * @param $name
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getUnusedBundle($name)
    {
        if (!isset($this->instantied_but_not_used_bundles[$name])) {
            $this->instantied_but_not_used_bundles[$name] = $this->instantiateBundle($name);
        }

        return $this->instantied_but_not_used_bundles[$name];
    }

    /**
     * A factory for bundle instances based on name.
     *
     * @param $name
     *
     * @throws \Exception
     *
     * @return ApiBundle|AppBundle|PortalBundle
     */
    private function instantiateBundle($name)
    {
        switch ($name) {
            case 'PortalBundle':
                return new PortalBundle();
            case 'ApiBundle':
                return new ApiBundle();
            case 'AppBundle':
                return new AppBundle();
            case 'AgentBundle':
                return new AgentBundle();
        }

        throw new \Exception('oops, that bundle cannot be auto-instantiated by DeskPRO\Kernel\BaseKernel');
    }
}
