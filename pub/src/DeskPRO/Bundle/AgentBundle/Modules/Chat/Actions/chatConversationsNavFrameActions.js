import { createAction } from 'Ampliflux/actions';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

export const loadMyChatConversationsCounts = createAction(
  'CHAT_LOAD_MY_CONVERSATIONS_COUNTS',
  trigger => Chat.loadMyChatConversationsCounts().then(promise => {
    trigger(promise.getData().data);
  })
);

export const loadAgentName = createAction(
  'CHAT_LOAD_AGENT_NAME',
  (trigger, id) => People.loadPerson(id).then(promise => {
    trigger({id, name: promise.getData().data.name});
  })
);

export const loadAllChatConversationsCounts = createAction(
  'CHAT_LOAD_ALL_CONVERSATIONS_COUNTS',
  trigger => Chat.loadAllChatConversationsCounts().then(promise => {
    trigger(promise.getData().data);

    const nested = promise.getData().data.nested.counts;
    for (let i = 0; i < nested.length; i++) {
      trigger(loadAgentName(nested[i].group));
    }
  })
);

export const toggleMyChatsGroupingControls = createAction('CHAT_TOGGLE_MY_CHATS_GROUPING_CONTROLS');