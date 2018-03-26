<?php

/**
 * DeskPRO.
 *
 * @category Apps
 */

namespace deskpro_us_saml;

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
            'sso_url'                     => $app->getSetting('sso_url'),
            'slo_url'                     => $app->getSetting('slo_url'),
            'issuer_id'                   => $app->getSetting('issuer_id'),
            'cert_fingerprint'            => $app->getSetting('cert_fingerprint'),
            'name_id_format'              => $app->getSetting('name_id_format'),
            'cert'                        => $app->getSetting('cert'),
            'login_custom_text'           => $app->getSetting('login_custom_text'),
            'raw_info_filter'             => $app->getSetting('raw_info_filter') ?: null,
            'sign_authn_request'          => $app->getSetting('sign_authn_request') ?: false,
            'sp_private_key'              => $app->getSetting('sp_private_key') ?: null,
            'sp_public_x509'              => $app->getSetting('sp_public_x509') ?: null,
            'include_custom_metadata_xml' => $app->getSetting('include_custom_metadata_xml') ?: false,
            'custom_metadata_xml'         => $app->getSetting('custom_metadata_xml') ?: null,
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
