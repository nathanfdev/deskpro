<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\People\UserPermissions\GroupDbPersister;
use Application\DeskPRO\People\UserPermissions\GroupsDbLoader;
use Application\DeskPRO\People\UserPermissions\UserPermissions;
use Application\DeskPRO\Usergroups\Form\Type\UsergroupType;
use Application\DeskPRO\Usergroups\UsergroupEdit;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Numbers;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ApiModes("all")
 */
class UsergroupsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction($type)
    {
        $data = [];

        if ($type == 'non_sys_user') {
            $data['groups'] = $this->em->getRepository(Usergroup::class)->getUsergroupNames();
        } else {
            $ugs = $this->em->createQuery('
                SELECT ug
                FROM DeskPRO:Usergroup ug
                WHERE ug.is_agent_group = false
                ORDER BY ug.title ASC
            ')->execute();
            $data['groups'] = $this->getApiData($ugs);
        }

        return $this->createApiResponse($data);
    }

    //##################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $usergroups = $this->container->getUserGroups();

        try {
            if (Numbers::isInteger($id)) {
                $usergroup = $usergroups->getGroup($id);
            } else {
                $usergroup = $usergroups->getSysGroup($id);
            }
        } catch (\Exception $e) {
            // handle not found
            $usergroup = null;
        }

        if (!$usergroup || $usergroup->is_agent_group) {
            throw $this->createNotFoundException();
        }

        $perms = new GroupsDbLoader([$usergroup], $this->em);

        $data          = $usergroup->toApiData();
        $data['perms'] = $perms->getGroupPermissions($usergroup->id);

        return $this->createApiResponse(['group' => $data]);
    }

    //##################################################################################################################
    // delete
    //###################################################################################################################

    public function deleteAction($id)
    {
        $usergroups = $this->container->getUserGroups();
        $usergroup  = $usergroups->getGroup($id);

        if (!$usergroup || $usergroup->is_agent_group) {
            throw $this->createNotFoundException();
        }

        if ($usergroup->sys_name) {
            return $this->createApiErrorResponse('no_delete_sys', 'You cannot delete built-in usergroups');
        }

        $this->em->remove($usergroup);
        $this->em->flush();

        $this->db->executeUpdate('DELETE FROM permissions_cache');

        return $this->createApiDeleteResponse(['old_group_id' => (int) $id]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        $usergroups = $this->container->getUserGroups();

        //------------------------------
        // Get group
        //------------------------------

        if ($id) {
            if (Numbers::isInteger($id)) {
                $usergroup = $usergroups->getGroup($id);
            } else {
                $usergroup = $usergroups->getSysGroup($id);
            }

            if (!$usergroup) {
                throw $this->createNotFoundException();
            }
        } else {
            $usergroup = new Usergroup();
        }

        //------------------------------
        // Save form
        //------------------------------

        $usergroup_edit = new UsergroupEdit($usergroup);

        $formData = ['group' => $this->in->getArrayValue('group')];
        unset($formData['group']['perms']);
        unset($formData['group']['dep_perms']);

        $form = $this->createForm(new UsergroupType(), $usergroup_edit, ['cascade_validation' => true]);
        $form->submit($formData, true);

        if (!$form->isValid()) {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }

        $usergroup_edit->save($this->em);

        //------------------------------
        // Save permissions
        //------------------------------

        // Save perms
        $perms = new UserPermissions();
        $perms->fromArray($this->in->getArrayValue('group.perms'));

        $db_persister = new GroupDbPersister($this->em);
        $db_persister->savePerms($usergroup, $perms);

        //------------------------------
        // Save department perms
        //------------------------------

        if ($this->in->checkIsset('group.dep_perms')) {
            $ticketDeps = $this->container->getTicketDepartments();
            $chatDeps   = $this->container->getChatDepartments();

            $setPerms = [];
            foreach ($this->in->getArrayValue('group.dep_perms.tickets') as $did => $p) {
                if (!$ticketDeps->getById($did)) {
                    continue;
                }
                if ($p['full']) {
                    $setPerms[] = ['department_id' => $did, 'usergroup_id' => $usergroup->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1, 'is_active' => 1];
                }
            }
            foreach ($this->in->getArrayValue('group.dep_perms.chat') as $did => $p) {
                if (!$chatDeps->getById($did)) {
                    continue;
                }
                if ($p['full']) {
                    $setPerms[] = ['department_id' => $did, 'usergroup_id' => $usergroup->id, 'app' => 'chat', 'name' => 'full', 'value' => 1, 'is_active' => 1];
                }
            }

            $this->db->executeUpdate('DELETE FROM department_permissions WHERE usergroup_id = ?', [$usergroup->id]);
            if ($setPerms) {
                $this->db->batchInsert('department_permissions', $setPerms, true);
            }
        }

        //------------------------------
        // Clear permission cache
        //------------------------------

        $this->db->executeUpdate('DELETE FROM permissions_cache');

        if (!$id) {
            return $this->createApiCreateResponse(
                ['id' => $usergroup->id],
                $this->generateUrl('api_user_groups_get', ['id' => $usergroup->id], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        } else {
            return $this->createApiSuccessResponse();
        }
    }

    public function savePermissionsAction($type)
    {
        $newPermissions = $this->in->getCleanValueArray('permissions');

        $userGroups = $this->container->getUserGroups()->getAll();

        $perms = new GroupsDbLoader($userGroups, $this->em);

        $db_persister = new GroupDbPersister($this->em);

        list($permissionType, $permissionAction) = explode('.', $type);

        foreach ($userGroups as $userGroup) {
            $permissions = $perms->getGroupPermissions($userGroup->getId());
            if (!isset($permissions->$permissionType->$permissionAction)) {
                return $this->createApiErrorResponse('unknown_permission', 'The permission '.$type.' does not exists');
            }
            if (!empty($newPermissions[$userGroup->getId()])) {
                $permissions->$permissionType->$permissionAction = true;
            } else {
                $permissions->$permissionType->$permissionAction = false;
            }
            $db_persister->savePerms($userGroup, $permissions);
        }

        return $this->createApiSuccessResponse();
    }
}
