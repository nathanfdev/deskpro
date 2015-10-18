import { createAction } from 'Ampliflux';
import Immutable from 'immutable';

import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

import * as recordStoreActions from 'Ampliflux/common/record-store/actions';

export const release = createAction('EG_WIDGET_TYPES_RELEASE', recordStoreActions.releaseRecords());
export const releaseRequest = createAction('EG_WIDGET_TYPES_RELEASE_REQ', recordStoreActions.releaseRequest());
export const setTypes = createAction('EG_WIDGET_TYPES_SET', recordStoreActions.setRequestRecords());

export const loadTypes = createAction('EG_WIDGET_TYPES_LOAD',
  recordStoreActions.createRecordsRequest(['RecordStores', 'Example', 'widgetTypes'], 'all', () => {
    return DpApi.sendGet('DP_API/sandbox_widget_types').then(result => {
      const records = result.getData().data.map(v => ({ id: v, name: v }));
      return records;
    })
  })
);
