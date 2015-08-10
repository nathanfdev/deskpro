import { createAction } from 'Ampliflux/actions';
import * as types from './actionTypes';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

export const loadMyChatConversationsCounts = createAction(
  types.CHAT_LOAD_MY_CONVERSATIONS_COUNTS,
  trigger => Chat.loadMyChatConversationsCounts().then(promise => {
    trigger(promise.getData().data);
  })
);

export function loadAllChatConversationsCounts() {
  return (dispatch, getState) => {
    Chat.loadAllChatConversationsCounts().then(promise => {
      dispatch({
        type: types.CHAT_LOAD_ALL_CONVERSATIONS_COUNTS,
        payload: promise.getData().data
      });

      const nested = promise.getData().data.nested.counts;
      for (let i = 0; i < nested.length; i++) {
        Chat.loadAllChatConversationsCounts().then(promise => {
          dispatch({
            type: types.CHAT_LOAD_AGENT_NAME,
            payload: {
              id: nested[i].group,
              name: 'Agent-' + Math.random()
            }
          });
        });
      }
    });
  }
}

