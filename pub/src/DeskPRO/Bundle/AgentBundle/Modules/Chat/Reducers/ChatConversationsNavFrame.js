import { Reducer } from 'Ampliflux/reducers';
import * as types from '../Actions/actionTypes';

export default class ChatConversationsNavFrame extends Reducer {
  getInitialState() {
    return {
      myChats:  {count: 0, nested: [/* {count, group} */]},
      allChats: {count: 0, nested: [/* {count, group, label} */]},
    };
  }

  registerHandlers() {
    this
      .r(types.CHAT_LOAD_MY_CONVERSATIONS_COUNTS, this.myConversationsCountsLoaded)
      .r(types.CHAT_LOAD_ALL_CONVERSATIONS_TOTAL, this.allConversationsTotalLoaded)
      .r(types.CHAT_LOAD_ALL_CONVERSATIONS_COUNT, this.allConversationsCountLoaded)
    ;
  }

  myConversationsCountsLoaded(state, {payload}) {
    let newState = {...state};
    newState.myChats = {count: payload.count, nested: payload.nested.counts};

    return newState;
  }

  allConversationsTotalLoaded(state, {payload}) {
    let newState = {...state};
    newState.allChats.count = payload;
    newState.allChats.nested.length = 0;

    return newState;
  }

  allConversationsCountLoaded(state, {payload}) {
    let newState = {...state};
    newState.allChats.nested.push(payload);

    return newState;
  }
}
