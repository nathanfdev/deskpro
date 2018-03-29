<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter;

use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\AgentLinkCustom;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\AgentLinkRoute;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class LinkConfigAnnotationRepo implements LinkConfigRepoInterface, CacheWarmerInterface
{
    const INTERNAL_DEFAULT = '__default__';

    /**
     * @var Reader
     */
    private $annotation_reader;

    /**
     * @var ConfigCache
     */
    private $config_cache;

    /**
     * @var array|null
     */
    private $cached_config_map;

    public function __construct(Reader $annotation_reader, ConfigCache $config_cache)
    {
        $this->annotation_reader = $annotation_reader;
        $this->config_cache      = $config_cache;
        $this->cached_config_map = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteInfo($object, $context, $type = null)
    {
        $type = $type ?: self::INTERNAL_DEFAULT;

        // load cached file, and read the array for get_class($object)
        // find the right context and type and return it
        // if it doesn't exist, try to find it on the entity itself

        if (!$info = $this->getAnnotationConfigFromCache($object)) {
            $info = $this->readAnnotationConfig($object);
        }

        if (!is_array($info) || empty($info)) {
            throw new ObjectRouterException(
                sprintf(
                    'There are no links defined for this entity in the "%s" context! Be sure to include a @%sLinkRoute or @%sLinkCustom annotation on the class "%s"',
                    $context,
                    ucfirst($context),
                    ucfirst($context),
                    get_class($object)
                )
            );
        }

        $object_info = current($info);

        if (!array_key_exists($context, $object_info)) {
            throw new ObjectRouterException(
                sprintf(
                    'There are no links defined for this entity in the "%s" context! Be sure to include a @%sLinkRoute or @%sLinkCustom annotation on the class "%s"',
                    $context,
                    ucfirst($context),
                    ucfirst($context),
                    get_class($object)
                )
            );
        }

        if (!array_key_exists($type, $object_info[$context])) {
            throw new ObjectRouterException(
                sprintf(
                    'There are no links defined for this entity for type "%s" in the "%s" context! Make sure you have a @%sLinkRoute or @%sLinkCustom annotation on the class "%s" with "type=%s"',
                    $type,
                    $context,
                    ucfirst($context),
                    ucfirst($context),
                    get_class($object),
                    $type
                )
            );
        }

        return $object_info[$context][$type];
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
     *               'param_map' => ['route' => 'property_path', 'another' => 'another_prop_path'],
     *            ],
     *            //...
     *         ]
     *         //..
     *    ]
     * ]
     *
     * @param $object_or_filename
     *
     * @return array
     */
    protected function readAnnotationConfig($object_or_filename)
    {
        $class = ClassUtils::getRealClass($this->parseClassName($object_or_filename));

        if (!$ref_class = new \ReflectionClass($class)) {
            throw new ObjectRouterException(sprintf('could not reflect on "%s" in file "%s"', $class, $object_or_filename));
        }

        $annotations = $this->annotation_reader->getClassAnnotations($ref_class);

        $result = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof PortalLinkRoute) {
                $result[$class][ObjectRouter::CONTEXT_PORTAL][$annotation->getType()] = $annotation->toRouteArray();
            } elseif ($annotation instanceof AgentLinkRoute) {
                $result[$class][ObjectRouter::CONTEXT_AGENT][$annotation->getType()] = $annotation->toRouteArray();
            } elseif ($annotation instanceof PortalLinkCustom) {
                $result[$class][ObjectRouter::CONTEXT_PORTAL][$annotation->getType()] = ObjectRouter::CONFIG_CUSTOM;
            } elseif ($annotation instanceof AgentLinkCustom) {
                $result[$class][ObjectRouter::CONTEXT_AGENT][$annotation->getType()] = ObjectRouter::CONFIG_CUSTOM;
            }
        }

        return $result;
    }

    /**
     * @return bool true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Return the full $config array (already parsed) from the cache file (done during warmup).
     *
     * @param $object_classname_or_filename
     *
     * @return array|null
     */
    protected function getAnnotationConfigFromCache($object_classname_or_filename)
    {
        $class = $this->parseClassName($object_classname_or_filename);

        // $this->config_cache->getPath() is the filename to the cached array
        // load it into memory if it is not already
        if (!isset($this->cached_config_map)) {
            // this file should always exist in production mode (cache/portal/objectRouter.php), so we'd only fail here
            // in dev mode (or if somehow it got to production without a proper warmup, which would be
            // a really big problem!)
            if (!file_exists($this->config_cache->getPath())) {
                $this->warmUp(null);
            }

            $this->cached_config_map = require $this->config_cache->getPath();
        }

        if (array_key_exists($class, $this->cached_config_map)) {
            return $this->cached_config_map[$class];
        }

        return;
    }

    /**
     * Will give you the FQCN of an object instance, a class file name, or the FQCN itself.
     *
     * @param $object_classname_or_filename
     *
     * @return false|string
     */
    protected function parseClassName($object_classname_or_filename)
    {
        if (is_object($object_classname_or_filename)) {
            $class = get_class($object_classname_or_filename);
        } elseif (class_exists($object_classname_or_filename)) {
            $class = $object_classname_or_filename;
        } elseif (file_exists($object_classname_or_filename)) {
            $class = $this->findClass($object_classname_or_filename);
        } else {
            $class = null;
        }

        return $class;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory - NOT USED, we already have the file cache path in the service
     */
    public function warmUp($cacheDir)
    {
        $link_config_dirs = [
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/Entity',
            DP_ROOT.'/src/Application/EmailBundle/Entity',
            DP_ROOT.'/src/Application/DeskPRO/Entity',
        ];

        if (is_dir($portalbundle = DP_ROOT.'/src/DeskPRO/Bundle/PortalBundle/Entity')) {
            $link_config_dirs[] = $portalbundle;
        }

        if (is_dir($apibundle = DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Entity')) {
            $link_config_dirs[] = $apibundle;
        }

        $finder = new Finder();
        $finder->files()->in($link_config_dirs)->name('*.php');

        $warmup_cache = [];
        /** @var SplFileInfo $php_file */
        foreach ($finder as $php_file) {
            if ($class = $this->parseClassName($php_file->getRealPath())) {
                $warmup_cache[$class] = $this->readAnnotationConfig($class);
            }
        }

        $this->config_cache->write('<?php return '.var_export($warmup_cache, true).';');
    }

    /**
     * The following is taken from symfony's Symfony\Component\Routing\Loader\AnnotationFileLoader class.
     *
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class     = false;
        $namespace = false;
        $tokens    = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; ++$i) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && is_array($token) && in_array($token[0], [T_NS_SEPARATOR, T_STRING]));
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
