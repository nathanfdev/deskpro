<?php

namespace Application\PortalBundle\Mode;

class PortalMode
{
    const ATTR_NAME = '_portal_mode';

    const MODE_ADMIN = 'admin';
    const MODE_NORMAL = 'normal';
    const MODE_BRAND = 'brand';

    protected $mode;
    protected $original_path;
    protected $internal_path;
    protected $mode_path;
    protected $data;

    public function __construct($path)
    {
        $this->mode = self::MODE_NORMAL;
        $this->original_path = $this->internal_path = $path;
    }

    public function __toString()
    {
        if ($this->isAdmin()) {
            return self::MODE_ADMIN;
        }

        if ($this->isBrand()) {
            return sprintf('%s [ID=%s]', self::MODE_BRAND, $this->getData());
        }

        return self::MODE_NORMAL;
    }

    public function __sleep()
    {
        return array(
            'mode',
            'original_path',
            'internal_path',
            'mode_path',
            'data'
        );
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

    public function setBrand($data)
    {
        $this->mode = self::MODE_BRAND;
        $this->data = $data;
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
