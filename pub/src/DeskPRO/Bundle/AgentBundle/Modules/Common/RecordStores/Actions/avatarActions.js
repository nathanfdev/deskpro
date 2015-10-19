import { createAction } from 'Ampliflux';
import { requestRecords, releaseRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

function createAvatarActions(type) {
  const load = createAction(
    'LOAD_AVATAR_' + type.toUpperCase(),
    requestRecords(
      ['RecordStores', 'Common', 'Avatars', type],
      missingIds => new Promise((resolve, reject) => DpApi
        .sendGet('DP_API/avatars/' + type + '?ids=' + missingIds.toArray().join(','))
        .success(response => resolve(response.data))
        .error(response => reject(response))
      )
    )
  );

  const release = createAction('RELEASE_AVATAR_' + type.toUpperCase(), releaseRequest());

  return [load, release];
}

export const [loadPersonAvatars, releasePersonAvatarsRequest] = createAvatarActions('person');
export const [loadOrganizationAvatars, releaseOrganizationAvatarsRequest] = createAvatarActions('organization');
export const [loadAgentTeamAvatars, releaseAgentTeamAvatarsRequest] = createAvatarActions('agent_team');
export const [loadDepartmentAvatars, releaseDepartmentAvatarsRequest] = createAvatarActions('department');