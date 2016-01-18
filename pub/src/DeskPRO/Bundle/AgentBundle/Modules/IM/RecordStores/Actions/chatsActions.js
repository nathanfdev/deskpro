import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { refreshCounts } from '../../Actions/messagesActions';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';


export const releaseChats = createAction('IM_RELEASE_CHATS', rsa.releaseRecords());
export const releaseRequest = createAction('IM_RELEASE_CHATS_REQUEST', rsa.releaseRequest());
export const setChatsRequest = createAction('IM_SET_CHATS', rsa.setRequestRecords());
export const loadChats = createAction(
  'IM_LOAD_CHATS',
  rsa.requestRecords(
    ['RecordStores', 'IM', 'chats'],
    (missingIds) => new Promise(
      (resolve, reject) =>
        IM.loadChats(missingIds)
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
          .success(response => {
            return resolve(response.data);
          })
          .error(response => reject(response))
    )
  )
);