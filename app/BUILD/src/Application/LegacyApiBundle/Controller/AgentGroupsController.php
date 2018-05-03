<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\People\AgentPermissions\AgentPermissions;
use Application\DeskPRO\People\AgentPermissions\GroupDbPersister;
use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;
use Application\DeskPRO\People\PermissionUtil;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;
use Symfony\Component\HttpFoundation\Response;

/**
 * Operations about agent groups
 * Simple CRUD controller.
 *
 * SWG\Resource(
 * 	resourcePath="/agent_groups",
 * 	description="Operations about agent groups",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class AgentGroupsController extends AbstractController implements ProtectedControllerInterface
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

    /**
     * @return Response
     *
     * SWG\Api(
     * 	path="/agent_groups",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get agent groups list",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
    public function listAction()
    {
        $ugs = $this->em->createQuery('
                SELECT ug
                FROM DeskPRO:Usergroup ug
                WHERE ug.is_agent_group = true
                ORDER BY ug.title ASC
            ')->execute();

        usort($ugs, function ($a, $b) {
            $ao = $a->sys_name ? 0 : 1;
            $bo = $b->sys_name ? 0 : 1;

            if ($ao == $bo) {
                $ao = $a->id;
                $bo = $b->id;
            }

            return $ao < $bo ? -1 : 1;
        });

        $data['groups'] = $this->getApiData($ugs);
        $ids            = array_map(function ($g) {
            return $g['id'];
        }, $data['groups']);

        if ($this->in->getBool('with_perms')) {
            $loader = new GroupsDbLoader($ids, $this->em);
            foreach ($data['groups'] as &$group) {
                $group['perms'] = $loader->getGroupPermissions($group['id']);
            }
        }

        return $this->createApiResponse($data);
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     *
     *
     * SWG\Api(
     * 	path="/agent_groups/{id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get group by ID",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Group ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
    public function getGroupAction($id)
    {
        $group = $this->em->find('DeskPRO:Usergroup', $id);

        if (!$group || !$group->is_agent_group) {
            throw $this->createNotFoundException();
        }

        $loader = new GroupsDbLoader([$group], $this->em);

        $data            = $group->toApiData();
        $data['members'] = [];
        $data['perms']   = $loader->getGroupPermissions($group->id)->toArray();

        $this->enablePermsForGroupOnArray($group, $data['perms']);

        $member_ids = $this->db->fetchAllCol('SELECT person_id FROM person2usergroups WHERE usergroup_id = ?', [$group->id]);
        if ($member_ids) {
            foreach ($member_ids as $pid) {
                $agent = $this->container->getAgentData()->get($pid);
                if ($agent) {
                    $data['members'][] = $agent->toBasicApiData();
                }
            }
        }

        return $this->createApiResponse(['group' => $data]);
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function saveGroupAction($id)
    {
        if ($id) {
            $is_new = false;
            $group  = $this->em->find('DeskPRO:Usergroup', $id);

            if (!$group || !$group->is_agent_group) {
                throw $this->createNotFoundException();
            }
        } else {
            $is_new                = true;
            $group                 = new Usergroup();
            $group->is_agent_group = true;
            $group->is_enabled     = true;
        }

        $group->title = $this->in->getString('group.title');

        //------------------------------
        // Save team
        //------------------------------

        $errors = $this->container->getValidator()->validate($group);
        if (count($errors)) {
            return $this->createApiValidationErrorResponse($errors);
        }

        $this->em->persist($group);
        $this->em->flush();

        //------------------------------
        // Save perms
        //------------------------------

        $perms = new AgentPermissions();
        $perms->fromArray($this->in->getArrayValue('group.perms'));

        $persister = new GroupDbPersister($this->em);
        $persister->savePerms($group, $perms);

        //------------------------------
        // Save members
        //------------------------------

        if ($is_new) {
            $current_members = [];
        } else {
            $current_members = $this->db->fetchAllCol('SELECT person_id FROM person2usergroups WHERE usergroup_id = ?', [$group->id]);
        }

        $new_members = $this->in->getArrayOfUInts('group.person_ids');
        $new_members = array_unique($new_members);
        $new_members = Arrays::removeFalsey($new_members);
        if ($new_members) {
            $agent_data  = $this->container->getAgentData();
            $new_members = array_filter($new_members, function ($a) use ($agent_data) {
                return $agent_data->get($a) ? true : false;
            });
        }

        $del_members = array_diff($current_members, $new_members);
        $new_members = array_diff($new_members, $current_members);

        if (!$is_new && $del_members) {
            $this->db->deleteIn('person2usergroups', $del_members, 'person_id', false, "usergroup_id = {$group->id}");
        }
        if ($new_members) {
            $ins = [];
            foreach ($new_members as $pid) {
                $ins[] = ['usergroup_id' => $group->id, 'person_id' => $pid];
            }
            $this->db->batchInsert('person2usergroups', $ins, true);
        }

        //------------------------------
        // Save department perms
        //------------------------------

        if ($this->in->checkIsset('dep_perms')) {
            $ticket_deps = $this->container->getTicketDepartments();
            $chat_deps   = $this->container->getChatDepartments();

            $set_perms = [];
            foreach ($this->in->getArrayValue('dep_perms.tickets') as $did => $p) {
                if (!$ticket_deps->getById($did)) {
                    continue;
                }
                if ($p['full']) {
                    $set_perms[] = ['department_id' => $did, 'usergroup_id' => $group->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1, 'is_active' => 1];
                } elseif ($p['assign']) {
                    $set_perms[] = ['department_id' => $did, 'usergroup_id' => $group->id, 'app' => 'tickets', 'name' => 'assign', 'value' => 1, 'is_active' => 1];
                }
            }
            foreach ($this->in->getArrayValue('dep_perms.chat') as $did => $p) {
                if (!$chat_deps->getById($did)) {
                    continue;
                }
                if ($p['full']) {
                    $set_perms[] = ['department_id' => $did, 'usergroup_id' => $group->id, 'app' => 'chat', 'name' => 'full', 'value' => 1, 'is_active' => 1];
                }
            }

            $this->db->executeUpdate('DELETE FROM department_permissions WHERE usergroup_id = ?', [$group->id]);
            if ($set_perms) {
                $this->db->batchInsert('department_permissions', $set_perms, true);
            }
        }

        //------------------------------
        // Clear permission cache
        //------------------------------

        $this->db->executeUpdate('DELETE FROM permissions_cache');

        $ag_perms_cache     = $this->db->fetchAllGrouped('SELECT usergroup_id, name FROM permissions', [], 'usergroup_id', null, 'name');
        $ag_dep_perms_cache = [
            'full'   => $this->db->fetchAllGrouped("SELECT usergroup_id, department_id FROM department_permissions WHERE name = 'full'", [], 'usergroup_id', null, 'department_id'),
            'assign' => $this->db->fetchAllGrouped("SELECT usergroup_id, department_id FROM department_permissions WHERE name = 'assign'", [], 'usergroup_id', null, 'department_id'),
        ];

        foreach ($new_members as $pid) {
            $a = $this->container->getAgentData()->get($pid);
            if ($a) {
                PermissionUtil::optimizePermissions($a, $ag_perms_cache, $ag_dep_perms_cache);
            }
        }

        //------------------------------
        // Return
        //------------------------------

        if ($is_new) {
            return $this->createApiCreateResponse(
                ['group_id' => $group->id],
                $this->generateUrl('api_agentgroups_get', ['id' => $group->id])
            );
        } else {
            return $this->createApiSuccessResponse([
                'group_id' => $group->id,
            ]);
        }
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     *
     *
     * SWG\Api(
     * 	path="/agent_groups/{id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete agent group by ID",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="id",
     *				description="Agent group ID",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
    public function deleteGroupAction($id)
    {
        $group = $this->em->find('DeskPRO:Usergroup', $id);

        if (!$group || !$group->is_agent_group || $group->sys_name) {
            throw $this->createNotFoundException();
        }

        $old_id = $group->id;
        $this->em->remove($group);
        $this->em->flush();

        return $this->createApiDeleteResponse(['old_group_id' => $old_id]);
    }

    //###################################################################################################################
    // get-all-perms
    //###################################################################################################################

    public function getAllPermsAction()
    {
        $ugs = $this->em->createQuery('
            SELECT ug
            FROM DeskPRO:Usergroup ug
            WHERE ug.is_agent_group = true
            ORDER BY ug.title ASC
        ')->execute();

        $loader = new GroupsDbLoader($ugs, $this->em);

        $group_data = [];

        foreach ($ugs as $ug) {
            $perms = $loader->getGroupPermissions($ug->id)->toArray();
            $this->enablePermsForGroupOnArray($ug, $perms);
            $group_data[] = [
                'group' => ['id' => $ug->id, 'title' => $ug->title],
                'perms' => $perms,
            ];
        }

        return $this->createApiResponse(['groups' => $group_data]);
    }

    /**
     * @param Usergroup $ug
     * @param array     $perms
     */
    private function enablePermsForGroupOnArray(Usergroup $ug, array &$perms)
    {
        if ($ug->sys_name != 'agent_all_perms' && $ug->sys_name != 'agent_all_safe_perms') {
            return;
        }

        foreach ($perms as &$set) {
            foreach ($set as $n => &$v) {
                if ($ug->sys_name != 'agent_all_safe_perms' || strpos($n, 'delete') === false) {
                    $v = true;
                }
            }
        }
    }

    //###################################################################################################################
    // toggle-group
    //###################################################################################################################

    public function toggleGroupAction($id, $is_enabled)
    {
        $group = $this->em->find('DeskPRO:Usergroup', $id);

        if (!$group || !$group->is_agent_group) {
            throw $this->createNotFoundException();
        }

        $group->is_enabled = (bool) $is_enabled;
        $this->em->persist($group);
        $this->em->flush();

        return $this->createSuccessResponse();
    }
}
