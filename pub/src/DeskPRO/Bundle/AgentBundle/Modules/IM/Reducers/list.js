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
    return state.set('agents', payload);
  },
  [actions.loadDepartments]: (state, payload) => {
    return state.set('departments', payload);
  },
  [actions.loadTeams]: (state, payload) => {
    return state.set('teams', payload);
  },
  [actions.loadRecentAgents]: (state, payload) => {
    return state.set('recentAgents', payload);
  }
});