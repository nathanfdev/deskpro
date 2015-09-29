<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\People\AgentPermissions\AgentPermissions;
use Application\DeskPRO\People\AgentPermissions\GroupDbPersister;
use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;
use Application\DeskPRO\People\AgentPermissions\PersonDbLoader as AgentPermsPersonDbLoader;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;

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
     * @return Response
     */
    public function settingsAction()
    {
        $agents = array();

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
        $ids    = array_map(function ($g) { return $g['id']; }, $groups);

        $loader = new GroupsDbLoader($ids, $this->em);
        foreach ($groups as &$group) {
            $group['perms'] = $loader->getGroupPermissions($group['id'])->toArray();
        }

        return $this->createApiResponse(array(
            'enabled' => $this->settings->get(self::KEY_ENABLED, 0),
            'agents'  => $agents,
            'groups'  => $groups,
        ));
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
