import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { loadAll, loadFromApi, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';

export const loadAgents = createAction(
  'ADMIN_LOAD_AGENTS',
  () => loadFromApi('Person', 'DP_API/agents', 'agents')
);

export const editAgent = createAction(
  'ADMIN_EDIT_AGENT',
  (id, data) => (dispatch, getState) => repository('Person').update(data, id).success(() => {
    const state = getState();
    const agents = agentsSelector(state);

    let agent = agents.get(id);
    if (!agent) {
      return;
    }

    agent = agent.mergeDeep(data);
    dispatch(updateCollection('Person', Immutable.List([agent]), 'merge'));
  })
);

export const loadAgentTeams = createAction(
  'ADMIN_LOAD_AGENT_TEAMS',
  () => loadFromApi('AgentTeam', 'DP_API/agent_teams', 'agent_teams')
);

export const loadAgentGroups = createAction(
  'ADMIN_LOAD_AGENT_GROUPS',
  () => dispatch => dispatch(loadAll('AgentGroup'))
);

export const loadTeamAgents = createAction(
  'ADMIN_LOAD_AGENT_TEAM_AGENTS',
  agentTeamId => api.sendGet(`DP_API/people?agent_team=${agentTeamId}`)
);

export const loadDepartmentAgents = createAction(
  'ADMIN_LOAD_DEPARTMENT_AGENTS',
  departmentId => api.sendGet(`DP_API/people?department=${departmentId}`)
);

export const loadGroupAgents = createAction(
  'ADMIN_LOAD_PERMISSION_GROUP_AGENTS',
  groupId => api.sendGet(`DP_API/people?usergroup=${groupId}`)
);
