import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseOrganizations = createAction('RELEASE_ORGANIZATIONS', rsa.releaseRecords());
export const releaseOrganizationsRequest = createAction('RELEASE_ORGANIZATIONS_REQUEST', rsa.releaseRequest());
export const setOrganizationsRequest = createAction('SET_ORGANIZATIONS', rsa.setRequestRecords());
export const loadOrganizations = createAction(
  'LOAD_ORGANIZATIONS',
  rsa.requestRecords(
    ['RecordStores', 'CRM', 'organizations'],
    missingIds => new Promise(
      (resolve, reject) =>
        api.sendGet('DP_API/organizations?ids=' + missingIds.toArray().join(','))
             .success(response => resolve(response.data))
             .error(response => reject(response))
    )
  )
);
