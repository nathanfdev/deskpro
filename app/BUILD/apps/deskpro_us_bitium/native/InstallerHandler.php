<?php

/**
 * DeskPRO.
 *
 * @category Apps
 */

namespace deskpro_us_bitium;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;

class InstallerHandler extends AbstractUsersourceInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    public function disableSsoSettings(AppInstance $app, EntityManager $em)
    {
        $settings             = $app->getSettings();
        $settings['sso_type'] = 'none';
        $app->setSettings($settings);

        $em->persist($app);
        $em->flush($app);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
    {
        $us->title   = $app->title;
        $us->options = [
            'sso_url'           => $app->getSetting('sso_url'),
            'slo_url'           => $app->getSetting('slo_url'),
            'issuer_id'         => $app->getSetting('issuer_id'),
            'cert'              => $app->getSetting('cert'),
            'login_custom_text' => $app->getSetting('login_custom_text'),
            'raw_info_filter'   => $app->getSetting('raw_info_filter') ?: null,
        ];
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->lost_password_url = '';
        $us->source_type       = 'Application\\DeskPRO\\Usersource\\Adapter\\Saml';

        $this->setupActions();

        if ('auto' == $app->getSetting('sso_type')) {
            $us->makeSsoAutoOnly();
        } elseif ('background' == $app->getSetting('sso_type')) {
            $us->makeSsoBackgroundOnly();
        } else {
            $us->disableSso();
        }

        $em->persist($us);
        $em->persist($app);
        $em->flush();
    }
}
