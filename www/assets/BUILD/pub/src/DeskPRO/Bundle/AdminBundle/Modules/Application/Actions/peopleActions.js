import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { loadFromApi, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
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
