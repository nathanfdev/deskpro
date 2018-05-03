<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_facebook;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;

class InstallerHandler extends AbstractUsersourceInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
    {
        $us->options = [
            'app_key'         => $app->getSetting('app_key'),
            'app_secret'      => $app->getSetting('app_secret'),
            'raw_info_filter' => $app->getSetting('raw_info_filter') ?: null,
        ];
        $us->lost_password_url = $app->getSetting('lost_pwd_url') ?: '';
        $us->title             = $app->title;
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->source_type       = 'Application\\DeskPRO\\Usersource\\Adapter\\Facebook';

        $this->setupActions();

        $em->persist($app);
        $em->persist($us);
        $em->flush();
    }
}
