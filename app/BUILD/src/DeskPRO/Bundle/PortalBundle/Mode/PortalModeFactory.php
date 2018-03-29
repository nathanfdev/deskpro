<?php

namespace DeskPRO\Bundle\PortalBundle\Mode;

/**
 * Generates a portal mode using URL paths. The request listener uses this to determine the portal mode.
 *
 * @see DeskPRO\Bundle\PortalBundle\Mode\PortalMode
 */
class PortalModeFactory
{
    const REGEX_ADMIN_PREVIEW = '#^/admin\-preview-([0-9]+?)(/{1}.*|$)$#';
    const REGEX_BRAND         = '#^/brand-([0-9]+?)(/{1}.*|$)$#';
    const REGEX_ADMIN         = '#^/admin\-mode(/{1}.*|$)$#';
    const REGEX_FOCUS_WIN     = '#^/focus\-win(/{1}.*|$)$#';
    const REGEX_FRAME_EMBED   = '#^/frame\-embed(/{1}.*|$)$#';

    public function createMode($path)
    {
        $mode = new PortalMode($path);

        if (preg_match(self::REGEX_ADMIN_PREVIEW, $path, $matches)) {
            $brand_id = (int) $matches[1];
            $mode->setBrand($brand_id);
            $mode->setAdminPreview();
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/admin-preview-%s', $brand_id));
        } elseif (preg_match(self::REGEX_BRAND, $path, $matches)) {
            // this will be deleted. brand won't be a mode. brand will be detected on the request listener
            // because it will depend on hostname. the brand stack is a separate thing.
            $brand_id = (int) $matches[1];
            $mode->setBrand($brand_id);
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/brand-%s', $brand_id));
        } elseif (preg_match(self::REGEX_ADMIN, $path, $matches)) {
            // this one will likely be deleted completely, no use at the moment
            $mode->setAdmin();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/admin-mode');
        } elseif (preg_match(self::REGEX_FOCUS_WIN, $path, $matches)) {
            $mode->setFocusWindow();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/focus-win');
        } elseif (preg_match(self::REGEX_FRAME_EMBED, $path, $matches)) {
            $mode->setFrameEmbed();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/frame-embed');
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
     *
     * @return string same as $pathinfo but stripped away the mode portion of the URL
     */
    public function getInternalPath($pathinfo)
    {
        $temp_mode = $this->createMode($pathinfo);

        return $temp_mode->getInternalPath();
    }
}
