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
 * @subpackage
 */

namespace Application\PortalBundle\Theme;

use Application\PortalBundle\Annotation\Tag as TagAnnotation;
use Application\PortalBundle\Themes\Base\BaseTheme;
use Application\PortalBundle\Themes\DevTest\DevTestTheme;
use Application\PortalBundle\Themes\Sidebar\SidebarTheme;
use Application\PortalBundle\Themes\Simple\SimpleTheme;
use Application\PortalBundle\Themes\Standard\StandardTheme;
use Application\PortalBundle\Themes\TabBar\TabBarTheme;
use Doctrine\Common\Annotations\FileCacheReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\ConfigCache;

/**
 * A reporistory of themes.
 * I can see this someday being a service, but we hardcode for now since we have so few.
 *
 * @package Application\PortalBundle\Theme
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
     * @var FileCacheReader
     */
    private $reader;

    public function __construct(ConfigCache $config_cache, FileCacheReader $reader, LoggerInterface $logger)
    {
        $this->config_cache = $config_cache;
        $this->logger = $logger;
        $this->reader = $reader;
        $this->theme_map = array();
    }

    /**
     * Get a theme by its ID. ie. $repo->find('standard')
     *
     * @param $id
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
     * A list of all themes in the order they are registered
     *
     * @return ThemeInterface[]
     */
    public function findAll()
    {
        if (count($this->theme_map) > 0) {
            return $this->theme_map;
        }

        // if fresh, we are done, return the stored array and retain it for easy access
        if (file_exists($this->config_cache) && !$this->config_cache->isFresh()) {
            $this->logger->debug('theme repository: unserializing from file cache');
           return $this->theme_map = require $this->config_cache;
        }

        $this->logger->debug('theme repository: creating theme map and then caching it for future use');

        // cache this
        $themes = array(
            new BaseTheme(),
            new StandardTheme(),
            new SimpleTheme(),
            new SidebarTheme(),
            new TabBarTheme(),
            new DevTestTheme(),
        );

        $this->theme_map = array();
        foreach ($themes as $theme) {
            $this->resolveTags($theme);
            $this->resolveParent($theme);
            $this->theme_map[$theme->getId()] = $theme;
        }

        $this->config_cache->write('<?php return unserialize(\'' . serialize($this->theme_map) . '\');');

        return $this->theme_map;
    }

    /**
     * Finds and adds all of the tags a theme can use to it. Static method calls and annotations supported.
     *
     * @param ThemeInterface $theme
     * @return Tag[]
     */
    private function resolveTags(ThemeInterface $theme)
    {
        $tags = array();

        // source 1: static method
        $static_tags = $theme::getTags();
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
            return (string)$a > (string)$b ? 1 : -1;
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
                            $name = $annotation->name;
                            $default_options = $annotation->default_options;
                            $esi = $annotation->esi;
                            $class_name = str_replace($refl->getNamespaceName().'\\', '', $refl->getName());
                            $class_name = str_replace('Controller', '', $class_name);
                            $method_name = str_replace('Action', '', $method->getName());
                            $callable = sprintf('%s:%s:%s', 'Theme', $class_name, $method_name);

                            $tag = new Tag($name, $callable, $default_options, $esi);
                            $tags[$tag->getName()] = $tag;
                        }
                    }
                }

            }
        }

        $theme->setTags($tags);
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
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
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
