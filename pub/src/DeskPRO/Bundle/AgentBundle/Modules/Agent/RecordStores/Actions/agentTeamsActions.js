import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseAgentTeams = createAction('RELEASE_AGENT_TEAMS', recordStoreActions.releaseRecords());
export const releaseRequest = createAction('RELEASE_AGENT_TEAMS_REQUEST', recordStoreActions.releaseRequest());
export const setAgentTeamsRequest = createAction('SET_AGENT_TEAMS_REQUEST', recordStoreActions.setRequestRecords());
