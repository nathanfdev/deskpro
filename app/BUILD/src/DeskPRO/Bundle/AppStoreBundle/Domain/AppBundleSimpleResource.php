<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class AppBundleSimpleResource implements AppBundleResource
{
    /** @var string */
    private $path;

    /** @var string */
    private $content;

    public function __construct($path, $content)
    {
        $this->path = $path;
        $this->content = $content;
    }

    function getPath()
    {
        return $this->path;
    }

    function getContent()
    {
        return $this->content;
    }

    function getFileExtension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    function getFileName()
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }
}
