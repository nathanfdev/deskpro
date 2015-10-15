import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const releaseTasks = createAction('RELEASE_TASKS', recordStoreActions.releaseRecords());
export const releaseTaskRequest = createAction('RELEASE_TASK_REQUEST', recordStoreActions.releaseRequest());
export const setTaskRequest = createAction('SET_TASKS_REQUEST', recordStoreActions.setRequestRecords());

export const loadAllTasks = createAction(
  'LOAD_ALL_TASKS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'Tasks', 'tasks'],
    'all',
    () => {
      return new Promise((resolve, reject) => {
        Tasks.loadAddress('tasks', {})
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
