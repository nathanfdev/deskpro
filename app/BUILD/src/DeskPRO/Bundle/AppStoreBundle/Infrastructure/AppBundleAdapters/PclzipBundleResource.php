<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppBundleResource;

class PclzipBundleResource implements AppBundleResource
{
    /** @var \PclZip */
    private $archive;

    /** @var array */
    private $entry;

    /** @var string */
    private $content;

    /**
     * PclzipBundleResource constructor.
     * @param \PclZip $archive
     * @param array $entry an item from the list returned by \PclZip::listContent
     */
    public function __construct( \PclZip $archive, array $entry)
    {
        $this->archive = $archive;
        $this->entry = $entry;
    }

    function getPath()
    {
        return $this->entry['stored_filename'];
    }


    function getFileExtension()
    {
        return pathinfo($this->getPath(), PATHINFO_EXTENSION);
    }

    function getFileName()
    {
        return pathinfo($this->getPath(), PATHINFO_FILENAME);
    }

    /**
     * Returns the content of the file
     *
     * @return string
     */
    function getContent()
    {
        if (! $this->content) {

            $extractor = new PclzipStringHandler();
            $this->content = $extractor->read($this->archive, $this->entry);
        }

        return $this->content;
    }
}
