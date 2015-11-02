import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const releaseTaskLabels = createAction('RELEASE_TASK_LABELS', recordStoreActions.releaseRecords());
export const releaseTaskLabelRequest = createAction('RELEASE_TASK_LABEL_REQUEST', recordStoreActions.releaseRequest());
export const setTaskLabelRequest = createAction('SET_TASK_LABELS_REQUEST', recordStoreActions.setRequestRecords());

export const loadAllTaskLabels = createAction(
  'LOAD_ALL_TASK_LABELS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'Tasks', 'taskLabels'],
    'allTaskLabels',
    () => {
      return new Promise((resolve, reject) => {
        Tasks.loadLabels()
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
