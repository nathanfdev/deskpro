<?php

namespace Application\PortalBundle\Mode;

class PortalModeFactory
{
    const REGEX_ADMIN = '#^/admin\-mode(/{1}.*|$)$#';
    const REGEX_BRAND = '#^/brand-([0-9]+?)(/{1}.*|$)$#';
    const REGEX_EMBED = '#^/embed-([0-9]+?)(/{1}.*|$)$#';

    public function createMode($path)
    {
        $mode = new PortalMode($path);

        if (preg_match(self::REGEX_ADMIN, $path, $matches)) {
            $mode->setAdmin();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/admin-mode');
        }

        if (preg_match(self::REGEX_BRAND, $path, $matches)) {
            $brand_id = (int)$matches[1];
            $mode->setBrand($brand_id);
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/brand-%s', $brand_id));
        }

        if (preg_match(self::REGEX_EMBED, $path, $matches)) {
            $code = (int)$matches[1];
            $mode->setEmbed($code);
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/embed-%s', $code));
        }

        return $mode;
    }
}
