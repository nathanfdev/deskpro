<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_jira;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractInstallerHandler;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;

class InstallerHandler extends AbstractInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    public function processSettings(InstallerContext $context, array $settings)
    {
        $old = $context->getApp()->getSettings() ?: [];

        return array_merge($old, $settings);
    }
}
