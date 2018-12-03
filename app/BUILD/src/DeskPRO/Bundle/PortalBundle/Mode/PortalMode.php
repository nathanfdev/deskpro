<?php

namespace DeskPRO\Bundle\PortalBundle\Mode;

/**
 * A portal mode represents a mode that the portal is in for a given request.
 *
 * There is always a portal mode. The default mode is "normal".
 *
 * @see \DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory
 */
class PortalMode
{
    const ATTR_NAME          = '_portal_mode';
    const MODE_ADMIN         = 'admin';
    const MODE_NORMAL        = 'normal';
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
    protected $originalPath;
    protected $internalPath;
    protected $modePath;
    protected $data;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->mode         = self::MODE_NORMAL;
        $this->originalPath = $this->internalPath = $path;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->isAdminPreview()) {
            return self::MODE_ADMIN_PREVIEW;
        }

        if ($this->isAdmin()) {
            return self::MODE_ADMIN;
        }

        if ($this->isFocusWindow()) {
            return self::MODE_FOCUS_WINDOW;
        }

        if ($this->isFrameEmbed()) {
            return self::MODE_FRAME_EMBED;
        }

        return self::MODE_NORMAL;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'mode',
            'originalPath',
            'internalPath',
            'modePath',
            'data',
        ];
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return self::MODE_ADMIN === $this->mode;
    }

    /**
     * @return bool
     */
    public function isNormal()
    {
        return self::MODE_NORMAL === $this->mode;
    }

    public function setAdmin()
    {
        $this->mode = self::MODE_ADMIN;
    }

    /**
     * @return bool
     */
    public function isAdminPreview()
    {
        return self::MODE_ADMIN_PREVIEW === $this->mode;
    }

    public function setAdminPreview()
    {
        $this->mode = self::MODE_ADMIN_PREVIEW;
    }

    /**
     * @return bool
     */
    public function isFocusWindow()
    {
        return self::MODE_FOCUS_WINDOW === $this->mode;
    }

    /**
     * @return bool
     */
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

    /**
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    /**
     * @param string $originalPath
     */
    public function setOriginalPath($originalPath)
    {
        $this->originalPath = $originalPath;
    }

    /**
     * @param string $internalPath
     */
    public function setInternalPath($internalPath)
    {
        $this->internalPath = $internalPath;
    }

    public function getInternalPath()
    {
        return $this->internalPath;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $modePath
     */
    public function setModePath($modePath)
    {
        $this->modePath = $modePath;
    }

    public function getModePath()
    {
        return $this->modePath;
    }
}
