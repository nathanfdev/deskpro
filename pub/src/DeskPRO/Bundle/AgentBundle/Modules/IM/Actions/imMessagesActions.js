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

export const gcMessages              = createAction('GC_MESSAGES',             recordStoreActions.gcRecords());
export const releaseMessages         = createAction('RELEASE_MESSAGES',        recordStoreActions.releaseRecords());
export const releaseMessageRequest   = createAction('RELEASE_MESSAGE_REQUEST', recordStoreActions.releaseRequest());
export const setMessagesRequest      = createAction('SET_MESSAGES_REQUEST',    recordStoreActions.setRequestRecords());

export const loadRecentMessages = createAction(
  'IM_LOAD_RECENT_MESSAGES',
  recordStoreActions.requestRecords(['IM', 'messages'], (entity_id, type) => {
    return new Promise((resolve) => {
      const recordMap = mapKeyedFromArray(messages, 'id');
      resolve(recordMap);
    });
  })
);

export const loadMessages = createAction(
  'IM_CHAT_LOAD_MESSAGES',
  (chat_id) => {
    return messages;
  }
);

export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chat_id, message) => {
    return   {
      author: {
        gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
      },
      text: 'test'
    };
  }
);



export const searchInChat = createAction(
  'IM_CHAT_SEARCH_IN_CHAT',
  () => {
  }
);