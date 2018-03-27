<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpCache\Configuration;

/**
 * Give the listener semantic meaning with this class name that this is a "tag" response cache.
 *
 * @Annotation
 */
class TagHttpCache extends PortalHttpCache
{
    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    public function getAliasName()
    {
        return 'portal_tag_cache';
    }

    /**
     * Returns whether multiple annotations of this type are allowed.
     *
     * @return bool
     */
    public function allowArray()
    {
        return false;
    }
}
