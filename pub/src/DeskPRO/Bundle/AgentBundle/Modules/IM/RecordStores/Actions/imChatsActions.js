import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';
import * as rsa from 'Ampliflux/common/record-store/actions';

import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';


export const releaseChats = createAction('IM_RELEASE_CHATS', rsa.releaseRecords());
export const releaseRequest = createAction('IM_RELEASE_CHATS_REQUEST', rsa.releaseRequest());
export const setChatsRequest = createAction('IM_SET_CHATS', rsa.setRequestRecords());
export const loadChats = createAction(
  'IM_LOAD_CHATS',
  rsa.createRecordsRequest(
    ['RecordStores', 'IM', 'chats'],
    'all',
    () => new Promise(
      (resolve, reject) =>
        IM.loadChats()
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);

export const loadRecentChats = createAction(
  'IM_LOAD_CHATS',
  rsa.createRecordsRequest(
    ['RecordStores', 'IM', 'chats'],
    'recent',
    () => new Promise(
      (resolve, reject) =>
        IM.loadRecentChats()
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);