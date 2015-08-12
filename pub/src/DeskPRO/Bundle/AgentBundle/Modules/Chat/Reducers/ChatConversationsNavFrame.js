import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatConversationsNavFrameActions';

export default class ChatConversationsNavFrame extends Reducer {
  getInitialState() {
    return {
      myChats:  {count: 0, nested: [/* {count, group} */]},
      myChatsGroupBy: 'date_period',
      isMyChatsGroupingControlVisible: false,

      allChats: {count: 0, nested: [/* {count, group} */]},
      allChatsGroupBy: 'agent',
      isAllChatsGroupingControlVisible: false,

      agentNames: {/* id: name */},
      departmentNames: {/* id: name */}
    };
  }

  registerHandlers() {
    this
      .r(actions.loadMyChatConversationsCounts, this.myConversationsCountsLoaded)
      .r(actions.loadAllChatConversationsCounts, this.allConversationsCountsLoaded)
      .r(actions.loadAgentName, this.agentNameLoaded)
      .r(actions.loadDepartmentName, this.departmentNameLoaded)
      .r(actions.toggleMyChatsGroupingControls, this.myChatsGroupingControlVisibilityChanged)
      .r(actions.toggleAllChatsGroupingControls, this.allChatsGroupingControlVisibilityChanged)
      .r(actions.changeMyChatsGrouping, this.myChatsGroupingChanged)
      .r(actions.changeAllChatsGrouping, this.allChatsGroupingChanged)
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

  departmentNameLoaded(state, {payload}) {
    let departmentNames = state.departmentNames;
    departmentNames = {...departmentNames, [payload.id]: payload.name};

    return {...state, departmentNames};
  }

  myChatsGroupingControlVisibilityChanged(state) {
    return {...state, isMyChatsGroupingControlVisible: !state.isMyChatsGroupingControlVisible}
  }

  allChatsGroupingControlVisibilityChanged(state) {
    return {...state, isAllChatsGroupingControlVisible: !state.isAllChatsGroupingControlVisible}
  }

  allChatsGroupingChanged(state, {payload}) {
    return {...state, allChatsGroupBy: payload}
  }

  myChatsGroupingChanged(state, {payload}) {
    return {...state, myChatsGroupBy: payload}
  }
}
