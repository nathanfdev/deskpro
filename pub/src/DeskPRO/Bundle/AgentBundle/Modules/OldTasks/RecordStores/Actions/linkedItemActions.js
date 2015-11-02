import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const releaseLinkedItems = createAction('RELEASE_LINKED_ITEMS', recordStoreActions.releaseRecords());
export const releaseLinkedItemRequest = createAction('RELEASE_LINKED_ITEM_REQUEST', recordStoreActions.releaseRequest());
export const setLinkedItemRequest = createAction('SET_LINKED_ITEMS_REQUEST', recordStoreActions.setRequestRecords());

export const loadLinkedItems = createAction('LOAD_LINKED_ITEMS', recordStoreActions.requestRecords(['RecordStores', 'Tasks', 'linkedItems'], (missingIds) => {
  return new Promise((resolve, reject) => {
    Tasks.loadLinks({
      ids: missingIds.join(',')
    })
      .success(response => resolve(response.data))
      .error(response => reject(response));
  });
}));

export const loadAllLinkedItems = createAction(
  'LOAD_LINKED_ITEMS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'Tasks', 'linkedItems'],
    'all',
    () => {
      return new Promise((resolve, reject) => {
        Tasks.loadLinks()
          .success(response => resolve(response.data))
          .error(response => reject(response));
      });
    }
  )
);
