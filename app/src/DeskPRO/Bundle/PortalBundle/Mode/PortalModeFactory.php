<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\Mode;

use Symfony\Component\Validator\Constraints\Url;

/**
 * Generates a portal mode using URL paths. The request listener uses this to determine the portal mode.
 */
class PortalModeFactory
{
    const REGEX_ADMIN         = '#^/admin\-mode(/{1}.*|$)$#';
    const REGEX_BRAND         = '#^/brand-([0-9]+?)(/{1}.*|$)$#';
    const REGEX_ADMIN_PREVIEW = '#^/admin\-preview(/{1}.*|$)$#';
    const REGEX_FOCUS_WIN     = '#^/focus\-win(/{1}.*|$)$#';

    public function createMode($path)
    {
        $mode = new PortalMode($path);

        if (preg_match(self::REGEX_ADMIN_PREVIEW, $path, $matches)) {
            $mode->setAdminPreview();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/admin-preview');
        } elseif (preg_match(self::REGEX_BRAND, $path, $matches)) {
            // this will be deleted. brand won't be a mode. brand will be detected on the request listener
            // because it will depend on hostname. the brand stack is a separate thing.
            $brand_id = (int) $matches[1];
            $mode->setBrand($brand_id);
            $mode->setInternalPath(strlen($matches[2]) > 0 ? $matches[2] : '/');
            $mode->setModePath(sprintf('/brand-%s', $brand_id));
        } elseif (preg_match(self::REGEX_ADMIN, $path, $matches)) {
            // this one will likely be deleted completey, no use at the moment
            $mode->setAdmin();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/admin-mode');
        } elseif (preg_match(self::REGEX_FOCUS_WIN, $path, $matches)) {
            $mode->setFocusWindow();
            $mode->setInternalPath(strlen($matches[1]) > 0 ? $matches[1] : '/');
            $mode->setModePath('/focus-win');
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
