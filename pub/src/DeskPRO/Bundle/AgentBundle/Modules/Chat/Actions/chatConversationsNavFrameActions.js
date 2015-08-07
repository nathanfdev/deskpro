import { createAction } from 'Ampliflux/actions';
import * as types from './actionTypes';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';

export const loadMyChatConversationsCounts = createAction(
  types.CHAT_LOAD_MY_CONVERSATIONS_COUNTS,
  trigger => Chat.loadMyChatConversationsCounts().then(promise => trigger(promise.getData().data))
);

export const loadAllChatConversationsCounts = createAction(
  types.CHAT_LOAD_ALL_CONVERSATIONS_COUNTS,
  trigger => Chat.loadAllChatConversationsCounts().then(promise => trigger(promise.getData().data))
);

export const loadAgentName = createAction(
  types.CHAT_LOAD_AGENT_NAME,
  (trigger, id) => trigger({id, name: 'Vasya'})
);