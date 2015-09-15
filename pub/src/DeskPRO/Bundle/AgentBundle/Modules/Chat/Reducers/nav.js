import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatNavActions';

const initialState = {
  lists: {
    my: {
      total: 0,
      groupBy: 'date_period',
      isGroupingControlVisible: false,
      items: [/* {count, group} */]
    },
    all: {
      total: 0,
      groupBy: 'agent',
      isGroupingControlVisible: false,
      items: [/* {count, group} */]
    }
  },

  agentNames: {/* id: name */},
  departmentNames: {/* id: name */}
};

export default createReducer(initialState, {

  [actions.loadCounts]: (state, payload, action) => {
    console.log(action.type + ': payload ', payload);
    return state.merge({
      lists: {
        [payload.list]: {
          total: payload.counts.count,
          items: payload.counts.nested
        }
      }
    });
  },

  [actions.loadAgentName]: (state, payload) => {
    let agentNames = state.get('agentNames');
    agentNames = {...agentNames, [payload.id]: payload.name};

    return state.merge({agentNames});
  },

  [actions.loadDepartmentName]: (state, payload) => {
    let departmentNames = state.get('departmentNames');
    departmentNames = {...departmentNames, [payload.id]: payload.name};

    return state.merge({departmentNames});
  },

  [actions.toggleListGroupingVisibility]: (state, payload) => {
    return state.merge({
      lists: {
        [payload]: {
          isGroupingControlVisible: !state.get('lists')[payload].isGroupingControlVisible
        }
      }
    });
  },

  [actions.changeListGrouping]: (state, payload) => {
    return state.merge({
      lists: {
        [payload.list]: {
          groupBy: payload.groupBy
        }
      }
    });
  }
});
