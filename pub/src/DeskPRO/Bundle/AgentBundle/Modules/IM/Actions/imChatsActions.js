import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import * as messagesActions from '../Actions/imMessagesActions';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import Immutable from 'immutable';

const messages = [
  {
    id: 1,
    author: {
      gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
    },
    text: 'test'
  },
  {
    id: 2,
    author: {
      gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
    },
    text: 'test'
  },
  {
    id: 3,
    author: {
      gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
    },
    text: 'test'
  }
];

export const gcChats              = createAction('GC_CHATS',             recordStoreActions.gcRecords());
export const releaseChats         = createAction('RELEASE_CHATS',        recordStoreActions.releaseRecords());
export const releaseChatRequest   = createAction('RELEASE_CHAT_REQUEST', recordStoreActions.releaseRequest());
export const setChatsRequest      = createAction('SET_CHATS_REQUEST',    recordStoreActions.setRequestRecords());

export const findChat = createAction(
  'IM_CHAT_FIND_CHAT',
  recordStoreActions.requestRecords(['IM', 'chats', 'agentChats'], (entity_id, type) => {
    return new Promise((resolve) => {
      const recordMap = mapKeyedFromArray(messages, 'id');
      resolve(recordMap);
    });
  })
);