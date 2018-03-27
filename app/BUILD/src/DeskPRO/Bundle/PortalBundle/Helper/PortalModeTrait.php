<?php

namespace DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait PortalModeTrait
{
    /**
     * @param ContainerInterface $container
     *
     * @return bool
     */
    private function isPreviewMode(ContainerInterface $container)
    {
        /** @var PortalModeStorage $portal_mode_storage */
        $portal_mode_storage = $container->get('portal_mode_storage');
        if ($mode = $portal_mode_storage->getMode()) {
            return $mode->isAdminPreview();
        }

        return false;
    }
}
