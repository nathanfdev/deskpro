<?php

namespace DeskPRO\Bundle\PortalBundle\Mode;

use Symfony\Component\Validator\Constraints\Url;

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
        } elseif (preg_match(self::REGEX_BRAND, $path, $matches)) {
            $brand_id = (int) $matches[1];
            $mode->setBrand($brand_id);
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/brand-%s', $brand_id));
        } elseif (preg_match(self::REGEX_EMBED, $path, $matches)) {
            $code = (int) $matches[1];
            $mode->setEmbed($code);
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/embed-%s', $code));
        } else {
            $mode->setInternalPath($path);
        }

        return $mode;
    }

    /**
     * A quick way to get the URL of a $pathinfo without the mode section. It will strip the mode portion of the URL
     * from the beginning if it can.
     *
     * @param string $pathinfo
     * @return string same as $pathinfo but stripped away the mode portion of the URL
     */
    public function getInternalPath($pathinfo)
    {
        $temp_mode = $this->createMode($pathinfo);

        return $temp_mode->getInternalPath();
    }
}
