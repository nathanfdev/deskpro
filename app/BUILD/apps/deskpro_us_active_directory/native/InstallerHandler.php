<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_active_directory;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use deskpro_us_active_directory\Usersource\AppOptionsMapper;
use Doctrine\ORM\EntityManager;

class InstallerHandler extends AbstractUsersourceInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
    {
        $us->title             = $app->title;
        $us->options           = AppOptionsMapper::getOptions($app);
        $us->lost_password_url = $app->getSetting('lost_pwd_url') ?: '';
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->source_type       = 'Application\\DeskPRO\\Usersource\\Adapter\\ActiveDirectory';
        $us->setSyncEnabled($app->getSetting('sync_enabled') ? true : false);
        $this->setupActions();

        $em->persist($app);
        $em->persist($us);
        $em->flush();
    }
}
