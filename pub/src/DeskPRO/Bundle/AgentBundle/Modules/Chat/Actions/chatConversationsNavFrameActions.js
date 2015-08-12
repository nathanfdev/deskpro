import { createAction } from 'Ampliflux/actions';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';

export const loadMyChatConversationsCounts = createAction(
  'CHAT_LOAD_MY_CONVERSATIONS_COUNTS',
  (trigger, groupBy) => Chat.loadMyChatConversationsCounts(groupBy).then(promise => {
    trigger(promise.getData().data);

    if (groupBy === 'department') {
      const nested = promise.getData().data.nested.counts;
      for (let i = 0; i < nested.length; i++) {
        trigger(loadDepartmentName(nested[i].group));
      }
    }
  })
);

export const loadAgentName = createAction(
  'CHAT_LOAD_AGENT_NAME',
  (trigger, id) => People.loadPerson(id).then(promise => {
    trigger({id, name: promise.getData().data.name});
  })
);

export const loadDepartmentName = createAction(
  'CHAT_LOAD_DEPARTMENT_NAME',
  (trigger, id) => Departments.loadDepartment(id).then(promise => {
    trigger({id, name: promise.getData().data.title});
  })
);

export const loadAllChatConversationsCounts = createAction(
  'CHAT_LOAD_ALL_CONVERSATIONS_COUNTS',
  (trigger, groupBy) => Chat.loadAllChatConversationsCounts(groupBy).then(promise => {
    trigger(promise.getData().data);

    if (groupBy === 'department') {
      const nested = promise.getData().data.nested.counts;
      for (let i = 0; i < nested.length; i++) {
        trigger(loadDepartmentName(nested[i].group));
      }
    }

    if (groupBy === 'agent') {
      const nested = promise.getData().data.nested.counts;
      for (let i = 0; i < nested.length; i++) {
        trigger(loadAgentName(nested[i].group));
      }
    }
  })
);

export const toggleMyChatsGroupingControls = createAction('CHAT_TOGGLE_MY_CHATS_GROUPING_CONTROLS');
export const toggleAllChatsGroupingControls = createAction('CHAT_TOGGLE_ALL_CHATS_GROUPING_CONTROLS');

export const changeMyChatsGrouping = createAction(
  'CHAT_CHANGE_MY_CHATS_GROUPING',
  (trigger, groupBy) => {
    trigger(groupBy);
    trigger(loadMyChatConversationsCounts(groupBy));
    trigger(toggleMyChatsGroupingControls());
  }
);
export const changeAllChatsGrouping = createAction(
  'CHAT_CHANGE_ALL_CHATS_GROUPING',
  (trigger, groupBy) => {
    trigger(groupBy);
    trigger(loadAllChatConversationsCounts(groupBy));
    trigger(toggleAllChatsGroupingControls());
  }
);