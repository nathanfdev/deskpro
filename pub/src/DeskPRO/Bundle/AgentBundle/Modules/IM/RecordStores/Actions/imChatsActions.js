import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';
import * as rsa from 'Ampliflux/common/record-store/actions';

import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';


export const releaseChats    = createAction('IM_RELEASE_CHATS',         rsa.releaseRecords());
export const releaseRequest  = createAction('IM_RELEASE_CHATS_REQUEST', rsa.releaseRequest());
export const setChatsRequest = createAction('IM_SET_CHATS',             rsa.setRequestRecords());
export const loadChats    = createAction(
  'IM_LOAD_CHATS',
  rsa.createRecordsRequest(
    ['RecordStores', 'IM', 'chats'],
    'all',
    () => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/agent_chats')
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);

export const loadRecentChats    = createAction(
  'IM_LOAD_CHATS',
  rsa.createRecordsRequest(
    ['RecordStores', 'IM', 'chats'],
    'recent',
    () => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/agent_chats')
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);