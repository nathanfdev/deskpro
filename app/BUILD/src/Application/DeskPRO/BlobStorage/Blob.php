<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\BlobStorage;

use Orb\Data\ContentTypes;
use Orb\Util\Strings;

class Blob
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $filename_safe = null;

    /**
     * @var string
     */
    protected $content_type;

    /**
     * @var array
     */
    protected $meta;

    public function __construct($filename, $content_type, array $meta = [])
    {
        $this->filename     = $filename;
        $this->content_type = $content_type;
        $this->meta         = $meta;

        // Automatically detect disposition if none provided
        if (!isset($this->meta['content_disposition'])) {
            if (ContentTypes::isInlineContentType($content_type, true, $filename)) {
                $this->meta['content_disposition'] = 'inline';
            } else {
                $this->meta['content_disposition'] = 'attachment';
            }
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return isset($this->meta['blob_id']) ? $this->meta['blob_id'] : null;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFilenameSafe()
    {
        if ($this->filename_safe === null) {
            $this->filename_safe = Strings::getFilenameSafe($this->filename);
        }

        return $this->filename_safe;
    }

    /**
     * @param string $id
     * @param mixed  $value
     */
    public function setMeta($id, $value)
    {
        if ($value === null) {
            unset($this->meta[$value]);
        } else {
            $this->meta[$id] = $value;
        }
    }

    /**
     * @param $id
     * @param null $default
     *
     * @return mixed
     */
    public function getMeta($id, $default = null)
    {
        return isset($this->meta[$id]) ? $this->meta[$id] : $default;
    }

    /**
     * @return array
     */
    public function getAllMeta()
    {
        return $this->meta;
    }
}
