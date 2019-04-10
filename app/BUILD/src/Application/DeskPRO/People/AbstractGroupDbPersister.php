<?php

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
            $permission->value     = $perms->getByName($p);
            $permission->is_active = true;
            $group->addPermission($permission);
        }

        // Some permissions might have changed value. Sync them (ex.: tickets.reopen_resolved_timelimit)
        foreach ($group->permissions as $permissionEntity) {
            // check once more permission exists saved values
            if (!in_array($permissionEntity->name, $setPermissions)) {
                continue;
            }
            $permValue = $perms->getByName($permissionEntity->name);
            // update only not bool values
            if (!is_bool($permValue)) {
                $permissionEntity->setValue($permValue);
            }
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
