import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';

export const releaseAgentTeams = createAction('RELEASE_AGENT_TEAMS', rsa.releaseRecords());
export const releaseRequest = createAction('RELEASE_AGENT_TEAMS_REQUEST', rsa.releaseRequest());
export const setAgentTeamsRequest = createAction('SET_AGENT_TEAMS_REQUEST', rsa.setRequestRecords());
