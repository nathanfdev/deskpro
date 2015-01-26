<?php

namespace Application\PortalBundle\Mode;

class PortalModeFactory
{
    const REGEX_ADMIN = '#^/admin\-mode(/{1}.*|$)$#';
    const REGEX_BRAND = '#^/brand-([0-9]+?)(/{1}.*|$)$#';

    public function createMode($path)
    {
        $mode = new PortalMode($path);

        if (preg_match(self::REGEX_ADMIN, $path, $matches)) {
            $mode->setAdmin();
            $mode->setInternalPath($matches[1]);
        }

        if (preg_match(self::REGEX_BRAND, $path, $matches)) {
            $brand_id = (int)$matches[1];
            $mode->setBrand($brand_id);
            $mode->setInternalPath($matches[2]);
        }

        return $mode;
    }
}
