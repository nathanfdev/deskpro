import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseProjects = createAction('RELEASE_PROJECTS', rsa.releaseRecords());
export const releaseProjectRequest = createAction('RELEASE_PROJECTS_REQUEST', rsa.releaseRequest());
export const setProjectRequest = createAction('SET_PROJECTS_REQUEST', rsa.setRequestRecords());

export const loadAllProjects = createAction(
  'LOAD_PROJECTS',
  rsa.createRecordsRequest(
    ['RecordStores', 'Tasks', 'projects'],
    'all',
    () => {
      return new Promise((resolve, reject) => {
        DpApi.sendGet('DP_API/projects')
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
