<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_magento;

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
        $settings               = $app->getSettings();
        $settings['enable_sso'] = false;
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
            'url'      => $app->getSetting('url'),
            'api_user' => $app->getSetting('api_user'),
            'api_key'  => $app->getSetting('api_key'),
            'sso_js'   => $app->getSetting('enable_sso') ? true : false,
        ];
        $us->lost_password_url = $app->getSetting('lost_pwd_url') ?: '';
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->source_type       = 'deskpro_magento\\Usersource\\Adapter\\Magento';

        $this->setupActions();

        if ($app->getSetting('enable_sso')) {
            $us->makeSsoBackgroundOnly();
        } else {
            $us->disableSso();
        }

        $em->persist($app);
        $em->persist($us);
        $em->flush();
    }
}
