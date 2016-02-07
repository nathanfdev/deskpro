import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export const releaseTaskLabels = createAction('RELEASE_TASK_LABELS', rsa.releaseRecords());
export const releaseTaskLabelRequest = createAction('RELEASE_TASK_LABEL_REQUEST', rsa.releaseRequest());
export const setTaskLabelRequest = createAction('SET_TASK_LABELS_REQUEST', rsa.setRequestRecords());

export const loadAllTaskLabels = createAction(
  'LOAD_TASK_LABELS',
  rsa.createRecordsRequest(
    ['RecordStores', 'Tasks', 'taskLabels'],
    'all',
    () => {
      return new Promise((resolve, reject) => {
        api.sendGet('DP_API/task_labels')
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
