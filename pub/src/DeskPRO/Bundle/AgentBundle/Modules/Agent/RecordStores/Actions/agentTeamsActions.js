import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseAgentTeams = createAction('RELEASE_AGENT_TEAMS', recordStoreActions.releaseRecords());
export const releaseRequest = createAction('RELEASE_AGENT_TEAMS_REQUEST', recordStoreActions.releaseRequest());
export const setAgentTeamsRequest = createAction('SET_AGENT_TEAMS', recordStoreActions.setRequestRecords());
export const loadAllAgentTeams = createAction(
  'LOAD_AGENT_TEAMS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'Agent', 'agentTeams'],
    'all',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/agent_teams')
           .success(response => resolve(response.data))
           .error(response => reject(response)))
  )
);

export const loadMyAgentTeams = createAction(
  'LOAD_AGENT_TEAMS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'Agent', 'agentTeams'],
    'my',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/agent_teams?my=true')
           .success(response => resolve(response.data))
           .error(response => reject(response)))
  )
);
