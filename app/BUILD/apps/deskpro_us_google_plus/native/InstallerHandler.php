<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_google_plus;

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
            'client_id'          => $app->getSetting('client_id') ?: null,
            'client_secret'      => $app->getSetting('client_secret') ?: null,
            'google_apps_domain' => $app->getSetting('google_apps_domain') ?: null,
            'raw_info_filter'    => $app->getSetting('raw_info_filter') ?: null,
        ];
        $us->is_enabled  = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->source_type = 'Application\\DeskPRO\\Usersource\\Adapter\\GooglePlus';

        $this->setupActions();

        $em->persist($app);
        $em->persist($us);
        $em->flush();
    }
}
