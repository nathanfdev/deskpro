<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\People\UserPermissions\Value\PermissionValueInterface;
use Doctrine\ORM\EntityManager;

abstract class AbstractGroupDbPersister
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    /**
     * @param Usergroup               $group
     * @param PermissionsSetInterface $perms
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function savePerms(Usergroup $group, PermissionsSetInterface $perms)
    {
        $permissionsArray   = $group->permissions->toArray();
        $currentPermissions = array_map(
            function (Permission $permission) {
                return $permission->name;
            },
            $permissionsArray
        );

        $setPermissions = [];
        foreach ($this->getPermissionsProperties() as $realName => $collName) {
            /** @var PermissionValueInterface $obj */
            $obj = $perms->$collName;
            foreach ($obj->getNames() as $prop) {
                if ($obj->$prop) {
                    $setPermissions[] = $realName.'.'.$prop;
                }
            }
        }

        $deletePermissions = array_diff($currentPermissions, $setPermissions);

        array_walk(
            $permissionsArray,
            function (Permission $permission, $key, $data) use ($group) {
                if (in_array($permission->name, $data)) {
                    $group->removePermission($permission);
                }
            },
            $deletePermissions
        );

        $newPermissions = array_diff($setPermissions, $currentPermissions);

        foreach ($newPermissions as $p) {
            $permission            = new Permission();
            $permission->usergroup = $group;
            $permission->name      = $p;
            $permission->value     = true;
            $permission->is_active = true;
            $group->addPermission($permission);
        }

        $this->em->persist($group);
        $this->em->flush();

        return true;
    }

    /**
     * @return array
     */
    abstract protected function getPermissionsProperties();
}
