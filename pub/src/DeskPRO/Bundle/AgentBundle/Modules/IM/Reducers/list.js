import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/imListActions.js';
import Immutable from 'immutable';

const initialState = {
  agents: [],
  teams: [],
  departments: [],
  recentAgents: [],
};

export default createReducer(initialState, {
  [actions.loadAgents]: (state, payload) => {
    return state.set('agents', []);
  },
  [actions.loadDepartments]: (state, payload) => {
    return state.set('departments', []);
  },
  [actions.loadTeams]: (state, payload) => {
    return state.set('teams', []);
  },
  [actions.loadRecentAgents]: (state, payload) => {
    return state.set('recentAgents', payload);
  }
});


//export default class IM_list extends Reducer {
//
//
//
//    registerHandlers() {
//        this.r(actions.loadAgents, this.listAgents);
//        this.r(actions.loadTeams, this.listTeams);
//        this.r(actions.loadDepartments, this.listDepartments);
//        this.r(actions.loadRecentAgents, this.listRecentAgents);
//    }
//
//    listAgents(prev, {payload}) {
//        return {...prev, agents: payload};
//    }
//
//    listTeams(prev, {payload}) {
//        return {...prev, teams: payload};
//    }
//
//    listDepartments(prev, {payload}) {
//        return {...prev, departments: payload};
//    }
//
//    listRecentAgents(prev, {payload}) {
//      console.log(payload);
//        return {...prev, recentAgents: payload};
//    }
//}
