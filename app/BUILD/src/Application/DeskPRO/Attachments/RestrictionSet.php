<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\Attachments;

use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Various tests that can be run on a file to see if we should accept it.
 */
class RestrictionSet
{
    const ERR_SIZE          = 'size';
    const ERR_FAIL_MUST_EXT = 'not_in_allowed_exts';
    const ERR_FAIL_NOT_EXT  = 'not_allowed_exts';

    /**
     * The max size to accept.
     *
     * @var int
     */
    protected $max_size = 5242880; // 5 MB

    /**
     * Whitelist of extention to accept.
     *
     * @var array
     */
    protected $allowed_exts = null;

    /**
     * Blacklist of extensions to reject.
     *
     * @var array
     */
    protected $disallowed_exts = null;

    /**
     * @param \Symfony\Component\HttpFoundation\File\File $file
     *
     * @return array|null
     */
    public function getError(File $file)
    {
        $size = $file->getSize();
        $ext  = Strings::getExtension($file->getFilename());

        if ($file instanceof UploadedFile) {
            if ($file->getClientOriginalName() === 'blob') {
                //workaround for images pasted directly from clipboard
                $guesser = new MimeTypeExtensionGuesser();
                $ext     = $guesser->guess($file->getClientMimeType());
            } else {
                $ext = Strings::getExtension($file->getClientOriginalName());
            }
        }

        return $this->getErrorForProperties([
            'size' => $size,
            'ext'  => $ext,
        ]);
    }

    /**
     * Check properties against this restriction set. $props can be:
     * - size (filesize)
     * - ext (file extension).
     *
     * @param array $props
     *
     * @return array|null
     */
    public function getErrorForProperties(array $props)
    {
        if (isset($props['size'])) {
            if ($this->max_size && $props['size'] > $this->max_size) {
                return [
                    'error_code'   => self::ERR_SIZE,
                    'error_detail' => Numbers::filesizeDisplay($this->max_size),
                ];
            }
        }

        if (isset($props['ext'])) {
            if ($this->allowed_exts && !in_array($props['ext'], $this->allowed_exts)) {
                return [
                    'error_code'   => self::ERR_FAIL_MUST_EXT,
                    'error_detail' => implode(',', $this->allowed_exts),
                ];
            }

            if ($this->disallowed_exts && in_array($props['ext'], $this->disallowed_exts)) {
                return [
                    'error_code'   => self::ERR_FAIL_NOT_EXT,
                    'error_detail' => implode(',', $this->disallowed_exts),
                ];
            }
        }

        return;
    }

    /**
     * @param array $allowed_exts
     *
     * @return $this
     */
    public function setAllowedExts(array $allowed_exts = null)
    {
        if ($allowed_exts) {
            $allowed_exts = \Orb\Util\Arrays::func($allowed_exts, 'trim');
        }
        $this->allowed_exts = $allowed_exts;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedExts()
    {
        return $this->allowed_exts;
    }

    /**
     * @param array $disallowed_exts
     *
     * @return $this
     */
    public function setDisallowedExts(array $disallowed_exts = null)
    {
        if ($disallowed_exts) {
            $disallowed_exts = \Orb\Util\Arrays::func($disallowed_exts, 'trim');
        }
        $this->disallowed_exts = $disallowed_exts;

        return $this;
    }

    /**
     * @return array
     */
    public function getDisallowedExts()
    {
        return $this->disallowed_exts;
    }

    /**
     * @param int $max_size
     *
     * @return $this
     */
    public function setMaxSize($max_size = null)
    {
        $this->max_size = (int) $max_size;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSize()
    {
        return $this->max_size;
    }
}
