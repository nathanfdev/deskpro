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
  return dispatch => {
    Chat.loadAllChatConversationsCounts().then(promise => {
      dispatch({
        type: types.CHAT_LOAD_ALL_CONVERSATIONS_TOTAL,
        payload: promise.getData().data.count
      });

      const nested = promise.getData().data.nested.counts;
      for (let i = 0; i < nested.length; i++) {
        //People.loadPerson(nested[i].group).then(promise => {
        //  nested[i].label = promise.getData().data.name;
        //  dispatch({
        //    type: types.CHAT_LOAD_ALL_CONVERSATIONS_COUNT,
        //    payload: nested[i]
        //  });
        //});

        nested[i].label = 'Agent-' + Math.random();
        dispatch({
          type: types.CHAT_LOAD_ALL_CONVERSATIONS_COUNT,
          payload: nested[i]
        });
      }
    });
  }
}

