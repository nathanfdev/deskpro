<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_db;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use deskpro_us_db\Usersource\AppOptionsMapper;
use Doctrine\ORM\EntityManager;

class InstallerHandler extends AbstractUsersourceInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    public function processSettings(InstallerContext $context, array $settings)
    {
        // PHP code on cloud must be set manually, so the web form
        // never changes it.

        if (defined('DPC_IS_CLOUD')) {
            $settings['php_code'] = '';

            if ($context->getApp()) {
                $settings['php_code'] = $context->getApp()->getSetting('php_code');
            }
        }

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
    {
        $us->title             = $app->title;
        $us->options           = AppOptionsMapper::getOptions($app);
        $us->lost_password_url = $app->getSetting('lost_pwd_url') ?: '';
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->source_type       = 'Application\\DeskPRO\\Usersource\\Adapter\\DbTablePhpPasswordCheck';

        $us->setSyncEnabled($app->getSetting('sync_enabled') ? true : false);
        $this->setupActions();

        $em->persist($app);
        $em->persist($us);
        $em->flush();
    }
}
