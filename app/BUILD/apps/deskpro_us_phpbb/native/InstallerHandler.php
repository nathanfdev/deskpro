<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_phpbb;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractUsersourceInstallerHandler;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use deskpro_us_phpbb\Usersource\AppOptionsMapper;
use Doctrine\ORM\EntityManager;

class InstallerHandler extends AbstractUsersourceInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em)
    {
        if (3 == $app->getSetting('phpbb_version')) {
            $type = 'Application\\DeskPRO\\Usersource\\Adapter\\PhpBb3';
        } else {
            $type = 'Application\\DeskPRO\\Usersource\\Adapter\\PhpBb2';
        }

        $us->title             = $app->title;
        $us->options           = AppOptionsMapper::getOptions($app);
        $us->is_enabled        = $app->getSetting('enable_usersource') ? 1 : 0;
        $us->lost_password_url = $app->getSetting('lost_pwd_url') ?: '';
        $us->source_type       = $type;

        $this->setupActions();

        $em->persist($us);
        $em->persist($app);
        $em->flush();
    }
}
