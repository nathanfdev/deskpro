<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\People\AgentPermissions\AgentPermissions;
use Application\DeskPRO\People\AgentPermissions\GroupDbPersister;
use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;
use Application\DeskPRO\People\AgentPermissions\PersonDbLoader as AgentPermsPersonDbLoader;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ProblemsController extends AbstractController implements ProtectedControllerInterface
{
    const KEY_ENABLED = 'core.problems.enabled';

    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    /**
     * get problems settings.
     *
     * @return JsonResponse
     */
    public function settingsAction()
    {
        $agents = [];

        foreach ($this->container->getAgentData()->getAgents() as $agent) {
            $agent_data          = $agent->toApiData();
            $perm_loader         = new AgentPermsPersonDbLoader($agent, $this->em);
            $agent_data['perms'] = $perm_loader->getEffectivePermissions()->toArray();
            $agents[]            = $agent_data;
        }

        $ugs = $this->em->createQuery('
                SELECT ug
                FROM DeskPRO:Usergroup ug
                WHERE ug.is_agent_group = true
                ORDER BY ug.title ASC
            ')->execute();

        $groups = $this->getApiData($ugs);
        $ids    = array_map(function ($g) {
            return $g['id'];
        }, $groups);

        $loader = new GroupsDbLoader($ids, $this->em);
        foreach ($groups as &$group) {
            $group['perms'] = $loader->getGroupPermissions($group['id'])->toArray();
        }

        return $this->createApiResponse([
            'enabled' => $this->settings->get(self::KEY_ENABLED, 0),
            'agents'  => $agents,
            'groups'  => $groups,
        ]);
    }

    /**
     * update problems settings.
     */
    public function updateSettingsAction()
    {
        $enabled = $this->in->getUInt('enabled');
        $this->settings->setSetting(self::KEY_ENABLED, $enabled);

        if (!$enabled) {
            return $this->settingsAction();
        }

        $groups = $this->in->getArrayValue('groups');
        foreach ($groups as $groupData) {
            if (!$group = $this->em->find('DeskPRO:Usergroup', $groupData['id'])) {
                continue;
            }

            $perms = new AgentPermissions();
            $perms->fromArray($groupData['perms']);
            $persister = new GroupDbPersister($this->em);
            $persister->savePerms($group, $perms);
        }

        $agents = $this->in->getArrayValue('agents');
        foreach ($agents as $agentData) {
            if (!$agent = $this->container->getAgentData()->get($agentData['id'])) {
                continue;
            }

            $perms = new AgentPermissions();
            $perms->fromArray($agentData['perms']);
            $persister = new GroupDbPersister($this->em);
            $persister->saveOverridePerms($agent, $perms);
        }

        return $this->settingsAction();
    }
}
