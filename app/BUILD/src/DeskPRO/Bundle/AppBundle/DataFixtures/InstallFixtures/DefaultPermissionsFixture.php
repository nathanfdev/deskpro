<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\People\UserPermissions\UserPermissions;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultPermissionsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    private $permNames;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 100;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $insertDepPerms = [];
        $insertPerms    = [];

        $specificPermissions = $this->getSpecificPermissions();

        foreach ([
                     $this->getReference('usergroup.everyone'),
                     $this->getReference('usergroup.registered'),
                 ] as $ug) {
            foreach (['support', 'sales'] as $id) {
                $dep              = $this->getReference('department.'.$id);
                $insertDepPerms[] = [
                    'usergroup_id'  => $ug->getId(),
                    'department_id' => $dep->getId(),
                    'name'          => 'full',
                    'value'         => 1,
                    'app'           => 'tickets',
                ];
            }
            foreach (['support', 'sales'] as $id) {
                $dep              = $this->getReference('chat_department.'.$id);
                $insertDepPerms[] = [
                    'usergroup_id'  => $ug->getId(),
                    'department_id' => $dep->getId(),
                    'name'          => 'full',
                    'value'         => 1,
                    'app'           => 'chat',
                ];
            }

            foreach ($this->getUsergroupPermNames() as $p) {
                $insertPerms[] = [
                    'usergroup_id' => $ug->getId(),
                    'name'         => $p,
                    'value'        => isset($specificPermissions[$ug->getId()][$p]) ?
                        $specificPermissions[$ug->getId()][$p] : 1,
                ];
            }
        }

        /** @var \Application\DeskPRO\DBAL\Connection $db */
        $db = $this->container->get('database_connection');
        $db->batchInsert('department_permissions', $insertDepPerms);
        $db->batchInsert('permissions', $insertPerms);
    }

    private function getUsergroupPermNames()
    {
        if ($this->permNames !== null) {
            return $this->permNames;
        }

        $perms = new UserPermissions();

        $setPerms = [];
        foreach (UserPermissions::$prefix_map as $realName => $collName) {
            $obj = $perms->$collName;
            foreach ($obj->getNames() as $prop) {
                $setPerms[] = $realName.'.'.$prop;
            }
        }

        return $this->permNames = $setPerms;
    }

    private function getSpecificPermissions()
    {
        return [
            $this->getReference('usergroup.everyone')->getId() => [
                'articles.share' => 0,
            ],
            $this->getReference('usergroup.registered')->getId() => [
            ],
        ];
    }
}
