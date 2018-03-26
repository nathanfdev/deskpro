<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Annotation\Tag as TagAnnotation;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Themes\Base\BaseTheme;
use DeskPRO\Bundle\PortalBundle\Themes\Sidebar\SidebarTheme;
use DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme;
use Doctrine\Common\Annotations\Reader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A reporistory of themes.
 * I can see this someday being a service, but we hardcode for now since we have so few.
 */
class ThemeRepository
{
    /**
     * @var array
     */
    private $theme_map;

    /**
     * @var ConfigCache
     */
    private $config_cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(ConfigCache $config_cache, Reader $reader, LoggerInterface $logger)
    {
        $this->config_cache = $config_cache;
        $this->logger       = $logger;
        $this->reader       = $reader;
        $this->theme_map    = [];
    }

    /**
     * Get a theme by its ID. ie. $repo->find('standard').
     *
     * @param $id
     *
     * @return ThemeInterface
     */
    public function find($id)
    {
        $themes = $this->findAll();

        if (!isset($themes[$id])) {
            throw new \InvalidArgumentException(sprintf('theme "%s" does not exist', $id));
        }

        $theme = $themes[$id];

        $this->resolveParent($theme);

        return $theme;
    }

    /**
     * A list of all themes in the order they are registered.
     *
     * @return ThemeInterface[]
     */
    public function findAll()
    {
        if (count($this->theme_map) > 0) {
            return $this->theme_map;
        }

        // if fresh, we are done, return the stored array and retain it for easy access
        if (file_exists($this->config_cache->getPath())) {
            $this->logger->debug('theme repository: unserializing from file cache');

            $this->theme_map = require $this->config_cache->getPath();

            // the cache only saves resolved tags, need to re-resolve parents
            foreach ($this->theme_map as $theme) {
                $this->resolveParent($theme);
            }

            return $this->theme_map;
        }

        $this->logger->debug('theme repository: creating theme map and then caching it for future use');

        // cache this
        $themes = [
            new BaseTheme(),
            new StandardTheme(),
            new SidebarTheme(),
        ];

        $this->theme_map = [];
        foreach ($themes as $theme) {
            $this->resolveTags($theme);
            $this->resolveParent($theme);
            $this->theme_map[$theme->getId()] = $theme;
        }

        $this->config_cache->write('<?php return unserialize(\''.serialize($this->theme_map).'\');');

        return $this->theme_map;
    }

    /**
     * Finds and adds all of the tags a theme can use to it. Static method calls and annotations supported.
     *
     * @param ThemeInterface $theme
     *
     * @return Tag[]
     */
    private function resolveTags(ThemeInterface $theme)
    {
        $tags = [];

        // source 1: static method
        $static_tags = $theme::getHardCodedTags();
        foreach ($static_tags as $tag) {
            $tags[$tag->getName()] = $tag;
        }

        // source 2: annotations
        $dir = $theme->getBaseControllerDir();
        if (!is_dir($dir)) {
            return $tags;
        }

        $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY));
        usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });
        foreach ($files as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }

                foreach ($refl->getMethods() as $method) {
                    $annotations = $this->reader->getMethodAnnotations($method);

                    foreach ($annotations as $annotation) {
                        if ($annotation instanceof TagAnnotation) {
                            $name                = $annotation->name;
                            $always_inline_guest = $annotation->always_guest_inline;
                            $allow_route_params  = $annotation->allow_route_params;
                            $default_options     = $annotation->default_options;
                            $esi                 = $annotation->esi;
                            $class_name          = str_replace($refl->getNamespaceName().'\\', '', $refl->getName());
                            $class_name          = str_replace('Controller', '', $class_name);
                            $method_name         = str_replace('Action', '', $method->getName());
                            $callable            = sprintf('%s:%s:%s', 'Theme', $class_name, $method_name);

                            $defined_options = $this->findTagOptions($refl->getName(), $method->getName());

                            $tag                   = new Tag($name, $callable, $defined_options, $default_options, $esi, $always_inline_guest, $allow_route_params);
                            $tags[$tag->getName()] = $tag;
                        }
                    }
                }
            }
        }

        $theme->setTags($tags);
    }

    private function findTagOptions($controller, $method)
    {
        $object = new \ReflectionClass($controller);
        $method = $object->getMethod($method);

        $annotations = $this->reader->getMethodAnnotations($method);

        $temp_options_resolver = new OptionsResolver();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof TagOptions) {
                if (count($annotation->defaults)) {
                    $temp_options_resolver->setDefaults($annotation->defaults);
                }

                if (count($annotation->required)) {
                    $temp_options_resolver->setRequired($annotation->required);
                }

                if (count($annotation->allowed_types)) {
                    $temp_options_resolver->setAllowedTypes($annotation->allowed_types);
                }

                if (count($annotation->allowed_values)) {
                    $temp_options_resolver->setAllowedValues($annotation->allowed_values);
                }

                break; // we only care about finding one TagOptions here
            }
        }

        return $temp_options_resolver->getDefinedOptions();
    }

    /**
     * This happens after resolveTags(). It is a second pass to fill in any missing parent tags to child tags.
     *
     * @param ThemeInterface $theme
     */
    private function resolveParent(ThemeInterface $theme)
    {
        if ($parent_id = $theme->getParentId()) {
            $theme->setParent($this->find($parent_id));
        }
    }

    /**
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
