<?php

namespace DeskPRO\Bundle\PortalBundle\Mode;

/**
 * A portal mode represents a mode that the portal is in for a given request.
 *
 * There is always a portal mode. The default mode is "normal".
 *
 * @see DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory
 */
class PortalMode
{
    const ATTR_NAME          = '_portal_mode';
    const MODE_ADMIN         = 'admin';
    const MODE_NORMAL        = 'normal';
    const MODE_BRAND         = 'brand';
    const MODE_ADMIN_PREVIEW = 'preview';

    /**
     * 'Focus' mode is when the window has a specific purpose, such as a login popup window
     * used from chat.
     */
    const MODE_FOCUS_WINDOW = 'focus-window';

    /**
     * Framed mode is a website embed option.
     */
    const MODE_FRAME_EMBED = 'frame-embed';

    protected $mode;
    protected $original_path;
    protected $internal_path;
    protected $mode_path;
    protected $data;

    public function __construct($path)
    {
        $this->mode          = self::MODE_NORMAL;
        $this->original_path = $this->internal_path = $path;
    }

    public function __toString()
    {
        if ($this->isAdminPreview()) {
            return self::MODE_ADMIN_PREVIEW;
        }

        if ($this->isAdmin()) {
            return self::MODE_ADMIN;
        }

        if ($this->isBrand()) {
            return sprintf('%s [ID=%s]', self::MODE_BRAND, $this->getData());
        }

        if ($this->isFocusWindow()) {
            return self::MODE_FOCUS_WINDOW;
        }

        if ($this->isFrameEmbed()) {
            return self::MODE_FRAME_EMBED;
        }

        return self::MODE_NORMAL;
    }

    public function __sleep()
    {
        return [
            'mode',
            'original_path',
            'internal_path',
            'mode_path',
            'data',
        ];
    }

    public function isAdmin()
    {
        return self::MODE_ADMIN === $this->mode;
    }

    public function isNormal()
    {
        return self::MODE_NORMAL === $this->mode;
    }

    public function isBrand()
    {
        return self::MODE_BRAND === $this->mode;
    }

    public function setAdmin()
    {
        $this->mode = self::MODE_ADMIN;
    }

    public function isAdminPreview()
    {
        return self::MODE_ADMIN_PREVIEW === $this->mode;
    }

    public function setAdminPreview()
    {
        $this->mode = self::MODE_ADMIN_PREVIEW;
    }

    public function setBrand($data)
    {
        $this->mode = self::MODE_BRAND;
        $this->data = $data;
    }

    public function isFocusWindow()
    {
        return self::MODE_FOCUS_WINDOW === $this->mode;
    }

    public function isFrameEmbed()
    {
        return self::MODE_FRAME_EMBED === $this->mode;
    }

    public function setFocusWindow()
    {
        $this->mode = self::MODE_FOCUS_WINDOW;
    }

    public function setFrameEmbed()
    {
        $this->mode = self::MODE_FRAME_EMBED;
    }

    public function getOriginalPath()
    {
        return $this->original_path;
    }

    public function setOriginalPath($original_path)
    {
        $this->original_path = $original_path;
    }

    public function setInternalPath($internal_path)
    {
        $this->internal_path = $internal_path;
    }

    public function getInternalPath()
    {
        return $this->internal_path;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setModePath($mode_path)
    {
        $this->mode_path = $mode_path;
    }

    public function getModePath()
    {
        return $this->mode_path;
    }
}
