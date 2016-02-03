import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as rsa from 'Ampliflux/common/record-store/actions';

export const release = createAction('EG_WIDGET_TYPES_RELEASE', rsa.releaseRecords());
export const releaseRequest = createAction('EG_WIDGET_TYPES_RELEASE_REQ', rsa.releaseRequest());
export const setTypes = createAction('EG_WIDGET_TYPES_SET', rsa.setRequestRecords());

export const loadTypes = createAction('EG_WIDGET_TYPES_LOAD',
  rsa.createRecordsRequest(
    ['RecordStores', 'Example', 'widgetTypes'],
    'all',
    () => api.sendGet('DP_API/sandbox_widget_types').then(result => result.getData().data.map(v => ({ id: v, name: v })))
  )
);
