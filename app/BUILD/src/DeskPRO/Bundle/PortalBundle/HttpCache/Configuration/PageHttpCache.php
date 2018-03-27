<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpCache\Configuration;

/**
 * Give the listener semantic meaning with this class name that this is a "page" response cache.
 *
 * @Annotation
 */
class PageHttpCache extends PortalHttpCache
{
    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    public function getAliasName()
    {
        return 'portal_page_cache';
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
