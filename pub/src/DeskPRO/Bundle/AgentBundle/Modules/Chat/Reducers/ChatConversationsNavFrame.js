import { Reducer } from 'Ampliflux/reducers';
import * as types from '../Actions/actionTypes';

export default class ChatConversationsNavFrame extends Reducer {
  getInitialState() {
    return {
      myChats:  {count: 0, nested: [/* {count, group} */]},
      allChats: {count: 0, nested: [/* {count, group} */]},
      agentNames: {/* id: name */}
    };
  }

  registerHandlers() {
    this
      .r(types.CHAT_LOAD_MY_CONVERSATIONS_COUNTS, this.myConversationsCountsLoaded)
      .r(types.CHAT_LOAD_ALL_CONVERSATIONS_COUNTS, this.allConversationsCountsLoaded)
      .r(types.CHAT_LOAD_AGENT_NAME, this.agentNameLoaded)
    ;
  }

  myConversationsCountsLoaded(state, {payload}) {
    let newState = {...state};
    newState.myChats = {count: payload.count, nested: payload.nested.counts};

    return newState;
  }

  allConversationsCountsLoaded(state, {payload}) {
    let newState = {...state};
    newState.allChats = {count: payload.count, nested: payload.nested.counts};

    return newState;
  }

  agentNameLoaded(state, {payload}) {
    let agentNames = state.agentNames;
    agentNames = {...agentNames, [payload.id]: payload.name};

    return {...state, agentNames};
  }
}
