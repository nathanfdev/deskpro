<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Departments\ChatDepartmentEdit;
use Application\DeskPRO\Departments\ChatDepartmentEditor;
use Application\DeskPRO\Departments\Form\Type\ChatDepartmentType;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Exception\ValidationException;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class ChatDepsController.
 *
 * @ApiModes("all")
 */
class ChatDepsController extends AbstractController implements ProtectedControllerInterface
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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $data = [];

        $chat_deps  = $this->container->getSystemService('chat_departments');
        $flat_array = $chat_deps->getFlatArray();

        $ag         = $this->container->getAgentGroups();
        $with_perms = $this->in->getBool('with_perms');

        if ($with_perms) {
            $perms = [];

            /** @var \Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService $ug */
            $ug = $this->container->getDataService('Usergroup');

            $all_perms = $this->db->fetchAll("
                SELECT department_id, usergroup_id, person_id, name
                FROM department_permissions
                WHERE app = 'chat'
            ");

            foreach ($all_perms as $p) {
                if (!isset($perms[$p['department_id']])) {
                    $perms[$p['department_id']] = ['agentgroups' => [], 'usergroups' => [], 'users' => []];
                }

                if ($p['usergroup_id']) {
                    if ($ug->getAgentGroup($p['usergroup_id'])) {
                        $perms[$p['department_id']]['agentgroups'][] = ['id' => (int) $p['usergroup_id'], 'name' => $p['name']];
                    } else {
                        $perms[$p['department_id']]['usergroups'][] = ['id' => (int) $p['usergroup_id'], 'name' => $p['name']];
                    }
                } else {
                    $perms[$p['department_id']]['users'][] = ['id' => (int) $p['person_id'], 'name' => $p['name']];
                }
            }
        }

        $deps = [];

        foreach ($flat_array as $row) {
            $r          = $row['object']->toApiData(true, false);
            $r['depth'] = $row['depth'];

            if ($with_perms) {
                if (isset($perms[$r['id']])) {
                    $r['permissions'] = $perms[$r['id']];
                } else {
                    $r['permissions'] = [];
                }

                if (!isset($r['permissions']['agentgroups'])) {
                    $r['permissions']['agentgroups'] = [];
                }
                $r['permissions']['agentgroups'][] = ['id' => $ag->getSysGroup('agent_all_perms')->id, 'name' => 'full'];
                $r['permissions']['agentgroups'][] = ['id' => $ag->getSysGroup('agent_all_safe_perms')->id, 'name' => 'full'];
            }

            $deps[] = $r;
        }

        $data['departments'] = $deps;

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        /*
         * @var \Application\DeskPRO\Departments\ChatDepartments
         */
        $chat_deps = $this->container->getSystemService('chat_departments');
        $dep       = $chat_deps->getById($id);

        if (!$dep || !$dep->is_chat_enabled) {
            throw $this->createNotFoundException();
        }

        $data                = [];
        $data['department']  = $this->getApiData($dep);
        $data['permissions'] = $chat_deps->getPermissionsInfo($dep);

        $ag                                   = $this->container->getAgentGroups();
        $data['permissions']['agentgroups'][] = ['usergroup_id' => $ag->getSysGroup('agent_all_perms')->id, 'perm_name' => 'full'];
        $data['permissions']['agentgroups'][] = ['usergroup_id' => $ag->getSysGroup('agent_all_safe_perms')->id, 'perm_name' => 'full'];

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws ValidationException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction($id)
    {
        if ($id) {

            /*
             * @var \Application\DeskPRO\Departments\ChatDepartments
             */
            $chat_deps = $this->container->getSystemService('chat_departments');
            $dep       = $chat_deps->getById($id);

            if (!$dep || !$dep->is_chat_enabled) {
                throw $this->createNotFoundException();
            }
        } else {
            $dep = Department::createChatDepartment();
        }

        $postData = $this->in->getAll('post');

        $chat_edit = new ChatDepartmentEdit($dep);

        $form = $this->createForm(new ChatDepartmentType(), $chat_edit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, ['department', 'permissions']), true);

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            if (!count($brand->getChatDepartments())) {
                return $this->createApiErrorResponse('validation_error', 'Brand '.$brand.' needs at least one department');
            }
        }

        if ($form->isValid()) {
            $chat_edit->save($this->em);

            $chat_edit->savePermissions(
                $this->em,
                $this->container->getAgentData()->getAgents(),
                $this->container->getDataService('Usergroup')->getAll()
            );
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }

        return $this->createApiResponse(
            [
                 'success' => true,
                 'id'      => $dep->id,
            ]
        );
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws ValidationException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($id)
    {
        /*
         * @var \Application\DeskPRO\Departments\ChatDepartments
         */
        $chat_deps = $this->container->getSystemService('chat_departments');
        $editor    = $this->_getDepartmentEditor();
        $dep       = $chat_deps->getById($id);

        if (!$dep) {
            throw $this->createNotFoundException();
        }

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            if (count($brand->getChatDepartments()) === 1 && $brand->hasDepartment($dep)) {
                return $this->createApiErrorResponse('validation_error', 'Brand '.$brand.' needs at least one department');
            }
        }

        $move_to_dep = $chat_deps->getById($this->in->getUint('move_to'));

        if (!$move_to_dep) {
            throw ValidationException::create('department.move_chat.dep_not_valid');
        }

        $old_id = $editor->removeDepartment($dep, $move_to_dep);

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getArrayOfUInts('display_orders');

        $editor = $this->_getDepartmentEditor();
        $editor->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }

    /**
     * @return ChatDepartmentEditor
     */
    private function _getDepartmentEditor()
    {
        $editor = new ChatDepartmentEditor($this->em);

        return $editor;
    }
}
