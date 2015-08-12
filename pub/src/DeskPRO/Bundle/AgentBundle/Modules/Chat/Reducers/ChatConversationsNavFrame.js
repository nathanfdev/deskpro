import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatConversationsNavFrameActions';

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
      .r(actions.loadMyChatConversationsCounts, this.myConversationsCountsLoaded)
      .r(actions.loadAllChatConversationsCounts, this.allConversationsCountsLoaded)
      .r(actions.loadAgentName, this.agentNameLoaded)
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
