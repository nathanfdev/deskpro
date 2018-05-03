<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

/**
 * An application bundle is conceptually virtually identical to a directory hierarchy. The major
 * difference is the bundle must have a manifest.json file in the top directory
 */
interface AppBundle
{
    /**
     * Returns the contents of the application manifest file as a string. if the bundle does not contain a manifest
     * file returns null
     *
     * @return string|null
     */
    function getManifestAsString();

    /**
     * Returns a list with all the resources packaged in this bundle
     *
     * @return AppBundleResource[];
     */
    function listAllResources();
}
