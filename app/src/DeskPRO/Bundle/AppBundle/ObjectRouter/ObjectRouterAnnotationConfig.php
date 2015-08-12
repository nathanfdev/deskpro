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

namespace DeskPRO\Bundle\AppBundle\ObjectRouter;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ObjectRouterAnnotationConfig implements ObjectRouterConfigInterface, CacheWarmerInterface
{
    const INTERNAL_DEFAULT = '__default__';

    /**
     * @var AnnotationReader
     */
    private $annotation_reader;

    /**
     * @var ConfigCache
     */
    private $config_cache;

    public function __construct(AnnotationReader $annotation_reader, ConfigCache $config_cache)
    {
        $this->annotation_reader = $annotation_reader;
        $this->config_cache = $config_cache;
    }

    /**
     * Returns an array like:
     * [
     *   'route' => 'route_name',
     *   'route_params' => ['param' => 'value']
     * ]
     *
     * @param object $object the entity/object itself
     * @param string $context the area: "portal", "agent".
     * @param string|null $type a specifier, since multiple routes can be configured
     * @return array
     */
    public function getRouteInfo($object, $context, $type = null)
    {
        $type = $type ?: self::INTERNAL_DEFAULT;

        // load cached file, and read the array for get_class($object)
        // find the right context and type and return it
        // if it doesn't exist, try to find it on the entity itself

        $info = $this->getAnnotationInfo($object);

        return $info[$context][$type];
    }

    /**
     * Given an object or filename return an array of info on it from annotations.
     *
     * The return format is like:
     * [
     *   'Fully\\Qualified\\Class\\Name' => [
     *        'context' => [
     *            'type' => [
     *               'route' => 'route_name',
     *               'route_params' => ['route' => 'property_path', 'another' => 'another_prop_path'],
     *            ],
     *            //...
     *         ]
     *         //..
     *    ]
     * ]
     *
     * @param $object_or_filename
     */
    protected function getAnnotationInfo($object_or_filename)
    {
        if (is_object($object_or_filename)) {
            $class = get_class($object_or_filename);
        } else {
            $class = $this->findClass($object_or_filename);
        }
    }

    /**
     * @return bool true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        // iterate thru all entity dirs for .php files
        // read info via getAnnotationInfo for each entity
        // once done collecting all cache data, store it in cache so getRouteInfo uses that
        //   instead of getAnnotationInfo
    }

    /**
     * The following is taken from symfony's Symfony\Component\Routing\Loader\AnnotationFileLoader class
     *
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; ++$i) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace . '\\' . $token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && is_array($token) && in_array($token[0], array(T_NS_SEPARATOR, T_STRING)));
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
