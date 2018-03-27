<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_jwt;

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

        $em->flush($app);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
    {
        $us->title   = $app->title;
        $us->options = [
            'url'               => $app->getSetting('url'),
            'secret'            => $app->getSetting('secret'),
            'algo'              => $app->getSetting('algo'),
            'login_custom_text' => $app->getSetting('login_custom_text'),
            'logout_agent_url'  => $app->getSetting('logout_agent_url'),
            'logout_user_url'   => $app->getSetting('logout_user_url'),
            'raw_info_filter'   => $app->getSetting('raw_info_filter') ?: null,
        ];
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->lost_password_url = $app->getSetting('url') ?: '';
        $us->source_type       = 'deskpro_us_jwt\\Usersource\\Adapter\\Jwt';

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
