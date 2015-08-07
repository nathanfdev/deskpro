import { Reducer } from 'Ampliflux/reducers';
import * as types from '../Actions/actionTypes';

export default class ChatConversationsNavFrame extends Reducer {
  getInitialState() {
    return {
      myChats:  {count: 0, nested: {grouped_by: '', counts: [/* {count, group} */]}},
      allChats: {count: 0, nested: {grouped_by: '', counts: [/* {count, group} */]}},
      agentNames: {/* agentId: name */}
    };
  }

  registerHandlers() {
    this
      .r(types.CHAT_LOAD_MY_CONVERSATIONS_COUNTS, this.setPayload('myChats'))
      .r(types.CHAT_LOAD_ALL_CONVERSATIONS_COUNTS, this.setPayload('allChats'))
      .r(types.CHAT_LOAD_AGENT_NAME, this.agentNameLoaded)
    ;
  }

  agentNameLoaded(state, action) {
    let agentNames = state.agentNames;
    agentNames[action.payload.id] = action.payload.name;

    return {...state, agentNames};
  }
}
