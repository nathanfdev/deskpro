<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Theme;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Representation of a DeskPRO Theme.
 */
abstract class AbstractTheme implements ThemeInterface, \Serializable
{
    /**
     * @var Tag[]
     */
    protected $tags;

    /**
     * @var ThemeInterface|null
     */
    protected $parent;

    public function serialize()
    {
        return serialize($this->tags);
    }

    public function unserialize($serialized)
    {
        $this->tags = unserialize($serialized);
    }

    /**
     * @param ThemeInterface $parent
     */
    public function setParent(ThemeInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        if ($this->getParentId() && !$this->parent) {
            throw new \RuntimeException(sprintf('theme "%s" was not constructed properly in the repository. It should have parent "%s" but does not.', $this->getId(), $this->getParentId()));
        }

        return $this->parent;
    }

    /**
     * Path to raw SCSS files on the filesystem.
     *
     * @return string
     */
    public function getStylesheetsPath()
    {
        return DP_WEB_ROOT.'/pub/src/DeskPRO/Bundle/PortalBundle/Resources/style';
    }

    /**
     * Get the tag for the given tag name.
     *
     * @param $tag_name
     *
     * @return Tag|null
     */
    public function getTag($tag_name)
    {
        return isset($this->tags[$tag_name]) ? $this->tags[$tag_name] : null;
    }

    /**
     * @param   $tag_name
     *
     * @return Tag|null will return null if tag doesn't exist for this theme or its parent heirarchy
     */
    public function resolveTag($tag_name)
    {
        if ($tag = $this->getTag($tag_name)) {
            return $tag;
        }

        if ($parent = $this->getParent()) {
            return $parent->resolveTag($tag_name);
        }

        return;
    }

    /**
     * A list of all tag objects.
     *
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param bool $skipCache
     *
     * @return array
     */
    public function getTemplateMap($skipCache = false)
    {
        if (!$skipCache) {
            $cacheFile = $this->getTemplateMapCachePath();
            if (file_exists($cacheFile)) {
                $map = require $cacheFile;

                return $this->getParent() ? array_merge($this->parent->getTemplateMap(), $map) : $map;
            }
        }

        $temps  = [];
        $parser = new YamlParser();

        if (is_dir($this->getBaseTemplateDir())) {
            $finder = new Finder();
            $finder->files()->name('*.twig')->in($this->getBaseTemplateDir());

            foreach ($finder as $temp) {
                // turn twig filename/path into Theme:x:y.html.twig syntax
                $path        = $temp->getRelativePathname();
                $path        = str_replace('\\', '/', $path);
                $path_broken = explode('/', $path);
                $controller  = array_shift($path_broken);
                if (count($path_broken) == 0) {
                    $name       = $controller;
                    $controller = '';
                } else {
                    $name = implode('/', $path_broken);
                }
                $template_name         = "Theme:$controller:$name";
                $resolved_path         = $temp->getRealPath();
                $resolved_path         = substr($resolved_path, strlen(realpath(DP_ROOT)));
                $temps[$template_name] = $resolved_path;

                if (file_exists($temp->getRealPath().'.yml')) {
                    $val = $parser->parse(file_get_contents($temp->getRealPath().'.yml'));
                    if (isset($val['show_tag_names'])) {
                        foreach ($val['show_tag_names'] as $tag_name) {
                            $temps['ThemeTagTemplate::'.$tag_name.'.html.twig'] = $resolved_path;
                        }
                    }
                }
            }
        }

        return $this->getParent() ? array_merge($this->parent->getTemplateMap(), $temps) : $temps;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $tags_with_names = [];

        foreach ($tags as $tag) {
            $tags_with_names[$tag->getName()] = $tag;
        }

        $this->tags = $tags_with_names;
    }

    /**
     * @return string
     */
    public function getTemplateMapCachePath()
    {
        return realpath($this->getBaseTemplateDir().'/../').'/templates_map_cache.php';
    }
}
