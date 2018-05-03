<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_twitter;

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
        $us->title   = $app->title;
        $us->options = [
            'app_key'         => $app->getSetting('consumer_key'),
            'app_secret'      => $app->getSetting('consumer_secret'),
            'raw_info_filter' => $app->getSetting('raw_info_filter') ?: null,
        ];
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->lost_password_url = $app->getSetting('lost_pwd_url') ?: '';
        $us->source_type       = 'Application\\DeskPRO\\Usersource\\Adapter\\Twitter';

        $this->setupActions();

        $em->persist($us);
        $em->persist($app);
        $em->flush();
    }
}
