import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const releaseProjects = createAction('RELEASE_PROJECTS', recordStoreActions.releaseRecords());
export const releaseProjectRequest = createAction('RELEASE_PROJECT_REQUEST', recordStoreActions.releaseRequest());
export const setProjectRequest = createAction('SET_PROJECTS_REQUEST', recordStoreActions.setRequestRecords());

export const loadAllProjects = createAction(
  'LOAD_PROJECTS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'Tasks', 'projects'],
    'all',
    () => {
      return new Promise((resolve, reject) => {
        Tasks.loadProjects()
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
