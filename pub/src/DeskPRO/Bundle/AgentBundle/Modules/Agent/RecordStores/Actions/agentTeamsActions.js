import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const gcAgentTeams         = createAction('GC_AGENT_TEAMS',              recordStoreActions.gcRecords());
export const releaseAgentTeams    = createAction('RELEASE_AGENT_TEAMS',         recordStoreActions.releaseRecords());
export const releaseRequest       = createAction('RELEASE_AGENT_TEAMS_REQUEST', recordStoreActions.releaseRequest());
export const setAgentTeamsRequest = createAction('SET_AGENT_TEAMS',             recordStoreActions.setRequestRecords());
export const loadAgentTeams       = createAction(
  'LOAD_AGENT_TEAMS',
  recordStoreActions.requestRecords(
    ['RecordStores', 'agentTeams'],
    missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/agent-teams?ids=' + missingIds.toArray().join(','))
             .success(response => resolve(response.data))
             .error(response => reject(response))
    )
  )
);
export const loadAllAgentTeams    = createAction(
  loadAgentTeams.type,
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'people'],
    'all',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/agent-teams')
           .success(response => resolve(response.data))
           .error(response => reject(response)))
  )
);
