<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface AppBundleResource
{
    /**
     * Returns the path of this file relative to the bundle root
     *
     * @return string
     */
    function getPath();

    /**
     * Returns the filename extension of this file
     *
     * @return string
     */
    function getFileExtension();

    /**
     * Returns the filename
     *
     * @return string
     */
    function getFileName();

    /**
     * Returns the content of the file
     *
     * @return string
     */
    function getContent();
}
