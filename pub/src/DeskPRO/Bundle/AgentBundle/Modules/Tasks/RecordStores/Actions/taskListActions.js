import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const releaseTaskLists = createAction('RELEASE_TASK_LISTS', rsa.releaseRecords());
export const releaseTaskListsRequest = createAction('RELEASE_TASK_LISTS_REQUEST', rsa.releaseRequest());
export const setTaskListsRequest = createAction('SET_TASK_LISTS_REQUEST', rsa.setRequestRecords());

export const loadAllTaskLists = createAction(
  'LOAD_TASK_LISTS',
  rsa.createRecordsRequest(
    ['RecordStores', 'Tasks', 'taskLists'],
    'all',
    () => {
      return new Promise((resolve, reject) => {
        api.sendGet('DP_API/task_lists')
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
