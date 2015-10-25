import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseLanguages = createAction('RELEASE_LANGUAGES', recordStoreActions.releaseRecords());
export const releaseLanguagesRequest = createAction('RELEASE_LANGUAGES_REQUEST', recordStoreActions.releaseRequest());
export const setLanguagesRequest = createAction('SET_LANGUAGES', recordStoreActions.setRequestRecords());

export const loadAll = createAction(
  'LOAD_LANGUAGES',
  createRecordsRequest(
    ['RecordStores', 'Common', 'languages'],
    'all',
    () => new Promise((resolve, reject) =>
        DpApi.sendGet('DP_API/languages')
          .success(response => resolve(response.data))
          .error((data, response) => reject(response))
    )
  )
);
