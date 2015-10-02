import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import { DpApi } from '../../../../Services/DpApi'
import Immutable from 'immutable';

export const gcChats              = createAction('GC_CHATS',             recordStoreActions.gcRecords());
export const releaseChats         = createAction('RELEASE_CHATS',        recordStoreActions.releaseRecords());
export const releaseChatRequest   = createAction('RELEASE_CHAT_REQUEST', recordStoreActions.releaseRequest());
export const setChatsRequest      = createAction('SET_CHATS_REQUEST',    recordStoreActions.setRequestRecords());

export const loadChats = createAction(
  'IM_CHAT_LOAD_CHATS',
  recordStoreActions.requestRecords(
    ['RecordStores', 'chats'],
    (missingIds) => new Promise(
      (resolve, reject) => {
        DpApi.sendGet('DP_API/agent_chats?test')
          .success(response => resolve(response.data))
          .error(response => reject(response));
      })
  )
);