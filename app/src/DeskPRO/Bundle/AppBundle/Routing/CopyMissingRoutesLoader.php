<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Routing;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * This lets us "share" routes between kernels.
 *
 * We have 3 kernels when this class was written. In order to have ApiKernel and PortalKernel and the main
 * DeskPRO kernel all be able to generate the same routes, we need a way to "share" them.
 *
 * For example, PortalBundle contains lots of routes. However, ApiKernel does NOT register the PortalBundle.
 * Because of this, ApiBundle and anything in ApiKernel cannot generate a "portal_" route unless we copy over
 * all the route config. We want to avoid this scenario.
 *
 * Instead, when a new route is added to PortalBundle (say, portal_search), then I should be instantly able to
 * GENERATE links to that route without me having to copy the config over to that kernel.
 *
 * The way we do this:
 *
 * 1. Find the main routing.yml file for the kernel
 * 2. Add a new route that has the type "share_route_names"
 * 3. Point to the main .yml file used by that other kernel
 *
 * We then go in and parse all of the routes and only include the ones not in our kernel. This loader must run last (be
 * the last "thing" on the main routing.yml file for that kernel).
 */
class CopyMissingRoutesLoader implements LoaderInterface
{
    /**
     * @varLoaderResolverInterface
     */
    private $resolver;

    /**
     * @var FileLocatorInterface
     */
    private $file_locator;

    public function __construct(FileLocatorInterface $file_locator)
    {
        $this->file_locator = $file_locator;
    }

    public function load($resource, $type = null)
    {
        $yml_loader = new YamlFileLoader($this->file_locator);
        $resolved = $yml_loader->load($resource);
        // if requirements:path = .* ignore
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return 'copy_missing_routes' === $type;
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
}
