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

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use Metadata\Cache\FileCache;
use Metadata\ClassMetadata;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ApiEndpointListener
{
    /**
     * @var ActionPermissionsDriver
     */
    protected $driver;

    /** @var string */
    protected $cache_dir;

    public function __construct(ActionPermissionsDriver $driver, $cache_dir)
    {
        $this->driver    = $driver;
        $this->cache_dir = $this->getCacheDir($cache_dir);
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $cache = new FileCache($this->cache_dir);
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $reflection = new \ReflectionClass($controller[0]);

        /** @var ClassMetadata $classMetadata */
        if (!$classMetadata = $cache->loadClassMetadataFromCache($reflection)) {
            $classMetadata = $this->driver->loadMetadataForClass($reflection);
            $cache->putClassMetadataInCache($classMetadata);
        }
        /** @var MethodMetadata $methodMetadata */
        $methodMetadata = $classMetadata->methodMetadata[$controller[1]];

        if ($methodMetadata && $methodMetadata instanceof MethodMetadata) {
            $modes = $methodMetadata->getModes();
            $tags  = $methodMetadata->getTags();
        }
    }

    protected function getCacheDir($cache_dir)
    {
        $cache_dir = str_replace('/api', '', $cache_dir).DIRECTORY_SEPARATOR.'api_permissions';
        if (!file_exists($cache_dir)) {
            if (!$rs = @mkdir($cache_dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $cache_dir));
            }
        }

        return $cache_dir;
    }
}
