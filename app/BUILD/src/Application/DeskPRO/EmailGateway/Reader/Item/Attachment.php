<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader\Item;

/**
 * Class Attachment.
 */
class Attachment
{
    /**
     * @var string
     */
    public $tmp_file;

    /**
     * @var callable
     */
    public $file_contents_callback;

    /**
     * @var string|null
     */
    public $file_contents = null;

    /**
     * @var string
     */
    public $file_name;

    /**
     * @var string
     */
    public $file_name_utf8;

    /**
     * @var string
     */
    public $mime_type;

    /**
     * @var string
     */
    public $original_charset; // with Rfc822 types

    /**
     * @var string ?
     */
    public $content_id;

    /**
     * @var string
     */
    public $ctype_primary;

    /**
     * @var string
     */
    public $ctype_secondary;

    /**
     * @var int
     */
    public $size;

    /**
     * @return string
     */
    public function getFileContents()
    {
        if ($this->file_contents !== null) {
            return $this->file_contents;
        } elseif ($this->file_contents_callback) {
            return call_user_func($this->file_contents_callback, $this);
        } elseif ($this->tmp_file) {
            return file_get_contents($this->tmp_file);
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getFileNameUtf8()
    {
        if (!$this->file_name_utf8) {
            return $this->getFileName();
        }

        return $this->file_name_utf8;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * @return string
     */
    public function getContentId()
    {
        return $this->content_id;
    }
}
