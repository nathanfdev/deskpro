<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Departments\Form\Type\TicketDepartmentType;
use Application\DeskPRO\Departments\TicketDepartmentEdit;
use Application\DeskPRO\Departments\TicketDepartmentEditor;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Settings\SettingHandler\TicketDepartment as TicketDepartmentHandler;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class TicketDepsController extends AbstractController implements ProtectedControllerInterface
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

    public function listAction()
    {
        $data = [];

        $ticket_deps = $this->container->getSystemService('ticket_departments');
        $flat_array  = $ticket_deps->getFlatArray();

        $ag         = $this->container->getAgentGroups();
        $with_perms = $this->in->getBool('with_perms');

        if ($with_perms) {
            $perms = [];

            /** @var \Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService $ug */
            $ug = $this->container->getDataService('Usergroup');

            $all_perms = $this->db->fetchAll("
                SELECT department_id, usergroup_id, person_id, name
                FROM department_permissions
                WHERE app = 'tickets'
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

        $deps_with_layout = $this->db->fetchAllCol('
            SELECT department_id
            FROM ticket_layouts
            WHERE department_id IS NOT NULL
        ');

        if ($deps_with_layout) {
            $deps_with_layout = array_fill_keys($deps_with_layout, true);
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

            if (isset($deps_with_layout[$r['id']])) {
                $r['has_layout'] = true;
            } else {
                $r['has_layout'] = false;
            }

            $deps[] = $r;
        }

        $data['departments'] = $deps;

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $dep = $this->container->getSystemService('ticket_departments')->getById($id);

        if (!$dep || !$dep->is_tickets_enabled) {
            throw $this->createNotFoundException();
        }

        $data               = [];
        $data['department'] = $this->getApiData($dep);

        $perms = $this->db->fetchAll('SELECT usergroup_id, person_id, name FROM department_permissions WHERE department_id = ?', [$dep->id]);

        $data['permissions'] = [
            'usergroups'  => [],
            'agentgroups' => [],
            'agents'      => [],
        ];

        foreach ($perms as $perm) {
            if ($perm['usergroup_id']) {
                if ($this->container->getDataService('Usergroup')->get($perm['usergroup_id'])->is_agent_group) {
                    $data['permissions']['agentgroups'][] = [
                        'usergroup_id' => (int) $perm['usergroup_id'],
                        'perm_name'    => $perm['name'],
                    ];
                } else {
                    $data['permissions']['usergroups'][] = [
                        'usergroup_id' => (int) $perm['usergroup_id'],
                        'perm_name'    => $perm['name'],
                    ];
                }
            } elseif ($perm['person_id']) {
                $data['permissions']['agents'][] = [
                    'agent_id'  => (int) $perm['person_id'],
                    'perm_name' => $perm['name'],
                ];
            }
        }

        $ag                                   = $this->container->getAgentGroups();
        $data['permissions']['agentgroups'][] = ['usergroup_id' => $ag->getSysGroup('agent_all_perms')->id, 'perm_name' => 'full'];
        $data['permissions']['agentgroups'][] = ['usergroup_id' => $ag->getSysGroup('agent_all_safe_perms')->id, 'perm_name' => 'full'];

        if ($this->in->getBool('with_agents_list')) {
            $data['agents_list'] = [];
            foreach ($this->container->getAgentData()->getAgents() as $agent) {
                $agent->loadHelper('AgentPermissions');
                if ($agent->getHelper('AgentPermissions')->isDepartmentAllowed($dep)) {
                    $data['agents_list'][] = $agent->toBasicApiData();
                }
            }
        }

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        if ($id) {
            $dep = $this->container->getSystemService('ticket_departments')->getById($id);

            if (!$dep || !$dep->is_tickets_enabled) {
                throw $this->createNotFoundException();
            }
        } else {
            $dep = Department::createTicketDepartment();
        }

        $dep_edit = new TicketDepartmentEdit($dep);

        $form = $this->createForm(
            new TicketDepartmentType(),
            $dep_edit,
            [
                'cascade_validation' => true,
            ]
        );

        $data = $this->in->getAll('post');
        $form->submit($data, true);

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            if (!count($brand->getTicketDepartments())) {
                return $this->createApiErrorResponse('validation_error', 'Brand '.$brand.' needs at least one department');
            }
        }

        if ($form->isValid() || 1) {
            if ($avatar_blob_id = $this->in->getUInt('department.avatar')) {
                $blob = $this->em->find(Blob::class, $avatar_blob_id);
                if ($blob && $blob->isImage()) {
                    $dep_edit->department->avatar = $blob;
                } else {
                    $dep_edit->department->avatar = null;
                }
            } else {
                $dep_edit->department->avatar = null;
            }

            $dep_edit->save($this->em);
            $dep_edit->savePermissions(
                $this->em,
                $this->container->getAgentData()->getAgents(),
                $this->container->getDataService('Usergroup')->getAll()
            );

            if (count($dep->children)) {
                //$dep_edit->clearTrigger($this->em);
            }

            return $this->createApiResponse(['id' => $dep->id, 'success' => true]);
        } else {
            $errors = [];

            foreach ($form->getErrors() as $er) {
                $errors[] = $er->getMessage();
            }

            return $this->createApiResponse(['department_id' => $dep->id, 'success' => false, 'errors' => $errors]);
        }
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        $editor = $this->_getDepartmentEditor();
        $dep    = $this->container->getSystemService('ticket_departments')->getById($id);

        if (!$dep) {
            throw $this->createNotFoundException();
        }

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            if (count($brand->getTicketDepartments()) === 1 && $brand->hasDepartment($dep)) {
                return $this->createApiErrorResponse('validation_error', 'Brand '.$brand.' needs at least one department');
            }
        }

        $move_to = $this->container->getSystemService('ticket_departments')->getById($this->in->getUint('move_to'));
        if (!$move_to) {
            throw ValidationException::create('department.remove.move_tickets', 'You must select a department to move existing tickets into');
        }

        $old_id = $editor->removeDepartment($dep, $move_to);

        return $this->createApiResponse(['old_id' => $old_id, 'success' => true]);
    }

    //###################################################################################################################
    // save-display-order
    //###################################################################################################################

    public function saveDisplayOrderAction()
    {
        $display_orders = $this->in->getArrayOfUInts('display_orders');

        $editor = $this->_getDepartmentEditor();
        $editor->updateDisplayOrders($display_orders);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get-settings
    //###################################################################################################################

    public function getSettingsAction()
    {
        $settings = $this->_getDepartmentSettingHandler()->getSettings();

        return $this->createApiResponse($settings);
    }

    //###################################################################################################################
    // save-settings
    //###################################################################################################################

    public function saveSettingsAction()
    {
        $set_settings = $this->in->getCleanValueArray('settings', 'string', 'string');
        $this->_getDepartmentSettingHandler()->setSettings($set_settings);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################

    /**
     * @param $id
     *
     * @throws
     *
     * @return TicketDepartmentEditor
     */
    private function _getDepartmentEditor()
    {
        $editor = new TicketDepartmentEditor($this->em);

        return $editor;
    }

    /**
     * @return TicketDepartmentHandler
     */
    public function _getDepartmentSettingHandler()
    {
        $ticket_deps_settings = new TicketDepartmentHandler(
            $this->container->getSettingsHandler(),
            $this->db
        );

        return $ticket_deps_settings;
    }
}
