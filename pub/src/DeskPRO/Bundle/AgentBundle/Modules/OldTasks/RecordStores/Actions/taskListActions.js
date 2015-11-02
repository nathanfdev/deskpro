import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const releaseTaskLists = createAction('RELEASE_TASK_LISTS', recordStoreActions.releaseRecords());
export const releaseTaskListRequest = createAction('RELEASE_TASK_LIST_REQUEST', recordStoreActions.releaseRequest());
export const setTaskListRequest = createAction('SET_TASK_LISTS_REQUEST', recordStoreActions.setRequestRecords());

export const loadTaskLists = createAction('LOAD_TASKS', recordStoreActions.requestRecords(['RecordStores', 'Tasks', 'tasks'], (missingIds) => {
  return new Promise((resolve, reject) => {
    Tasks.loadTasks({
      ids: missingIds.join(',')
    })
      .success(response => resolve(response.data))
      .error(response => reject(response));
  });
}));
