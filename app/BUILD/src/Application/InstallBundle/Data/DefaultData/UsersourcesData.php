<?php

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\Usersource;

/**
 * Class UsersourcesData.
 */
class UsersourcesData extends AbstractDefaultData
{
    /**
     * {@inheritdoc}
     */
    public function runInstallViaUpgrade()
    {
        // do nothing because the db upgrade script will do this (along with other things) for us
    }

    /**
     * {@inheritdoc}
     */
    public function runInstall()
    {
        $this->installDeskproUsersource();
    }

    /**
     * {@inheritdoc}
     */
    public function runReset()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function runSync()
    {
    }

    private function installDeskproUsersource()
    {
        $enabled = $this->getContainer()->getSetting('core.deskpro_source_enabled') ? 1 : 0;

        $types = ['user', 'agent'];
        foreach ($types as $type) {
            $deskProUsers                = new Usersource();
            $deskProUsers->type          = $type;
            $deskProUsers->source_type   = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO';
            $deskProUsers->is_enabled    = $enabled;
            $deskProUsers->display_order = -10; // ensure #1 order (initially!)
            $deskProUsers->title         = 'Deskpro';
            $deskProUsers->options       = [
                'reg_enabled' => true,
            ];
            $this->getEm()->persist($deskProUsers);
        }

        $this->getEm()->flush();
    }
}
