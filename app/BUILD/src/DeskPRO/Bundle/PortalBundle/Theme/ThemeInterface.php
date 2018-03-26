<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Theme;

/**
 * Representation of a DeskPRO Theme.
 */
interface ThemeInterface
{
    /**
     * Templates have a string identifier. "base", or "default", or "default-modified", etc. Must be unique.
     *
     * @return string
     */
    public function getId();

    /**
     * A human readable name of the theme (used in dropdown, reporting, etc).
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the parent of this theme. This theme will inherit templates/tags/controllers of the parent, and can
     * override them.
     *
     * @return ThemeInterface|null
     */
    public function getParent();

    /**
     * The theme ID of the parent theme.
     *
     * @return string|null
     */
    public function getParentId();

    /**
     * @return string absolute path to the root of this theme's templates
     */
    public function getBaseTemplateDir();

    /**
     * @return string absolute path to the root of this theme's controllers
     */
    public function getBaseControllerDir();

    /**
     * @return string absolute path to the root of this themes stylesheets
     */
    public function getStylesheetsPath();

    /**
     * @return string|null base namespace of controllers, like: DeskPRO\Bundle\PortalBundle\Themes\Standard
     */
    public function getNamespace();

    /**
     * Get the tag for the given tag name.
     *
     * @param $tag_name
     *
     * @return Tag|null
     */
    public function getTag($tag_name);

    /**
     * Similar to getTag() but it also recursivley climbs the tree until it finds the tag.
     *
     * @param $tag_name
     *
     * @return Tag|null
     */
    public function resolveTag($tag_name);

    /**
     * Return a list of all tag objects.
     *
     * @return Tag[]
     */
    public function getTags();

    /**
     * Get a map of "Theme:x:y.html.twig" => "/abs/path/to/source/twig/file.twig" for all templates that this theme
     * can resolve. This means recursively going through the parents for a complete list. See AbstractTheme.
     *
     * @return array
     */
    public function getTemplateMap();

    /**
     * Return an array of instantiated Tag objects for use within this theme.
     *
     * @return Tag[]
     */
    public static function getHardCodedTags();

    /**
     * Sets the parent of the theme.
     *
     * @param ThemeInterface $parent
     */
    public function setParent(ThemeInterface $parent);

    /**
     * Sets an array of tag objects, replaces previously set tags with new array.
     *
     * @param Tag[] $tags
     */
    public function setTags(array $tags);
}
