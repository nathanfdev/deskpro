<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_oauth2_proxy;

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
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        if (
            !defined('DPC_IS_CLOUD')
            && $DP_ENV->getConfig('env.server_id') !== 'builder.deskprodemo.com'
        ) {
            throw new \RuntimeException();
        }

        $us->title   = $app->title;
        $us->options = [
            'providers' => [
                'google'   => $app->getSetting('google'),
                'linkedin' => $app->getSetting('linkedin'),
                'azure'    => $app->getSetting('azure'),
            ],
        ];
        $us->is_enabled  = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->source_type = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskproOauth2Proxy';
        $us->type        = Usersource::TYPE_AGENT;
        $us->auto_agent  = false;

        $em->persist($app);
        $em->persist($us);
        $em->flush();
    }
}
