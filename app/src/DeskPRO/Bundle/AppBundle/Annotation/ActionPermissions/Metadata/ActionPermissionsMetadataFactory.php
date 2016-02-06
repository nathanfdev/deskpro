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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use Metadata\Cache\CacheInterface;
use Metadata\ClassMetadata;
use Metadata\MetadataFactoryInterface;

/**
 * Class ActionPermissionsMetadataFactory.
 */
class ActionPermissionsMetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var ActionPermissionsDriver
     */
    protected $driver;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var array
     */
    public $loaded_metadata = [];

    /**
     * @param ActionPermissionsDriver $driver
     */
    public function __construct(ActionPermissionsDriver $driver, CacheInterface $cache, $debug = false)
    {
        $this->debug  = $debug;
        $this->driver = $driver;
        $this->cache  = $cache;
    }

    /**
     * @param string $class_name
     * @param bool   $force_rewrite
     *
     * @return ClassMetadata
     */
    public function getMetadataForClass($class_name, $force_rewrite = false)
    {
        if (isset($this->loaded_metadata[$class_name]) && !$force_rewrite) {
            return $this->loaded_metadata[$class_name];
        }
        $reflection = new \ReflectionClass($class_name);
        if (null !== $classMetadata = $this->cache->loadClassMetadataFromCache($reflection)) {
            if ($this->debug || $force_rewrite) {
                $this->cache->evictClassMetadataFromCache($reflection);
            } else {
                $this->loaded_metadata[$class_name] = $classMetadata;

                return $this->loaded_metadata[$class_name];
            }
        }

        if (null !== $classMetadata = $this->driver->loadMetadataForClass($reflection)) {
            $this->loaded_metadata[$class_name] = $classMetadata;
            $this->cache->putClassMetadataInCache($classMetadata);

            return $this->loaded_metadata[$class_name];
        }

        return;
    }
}
