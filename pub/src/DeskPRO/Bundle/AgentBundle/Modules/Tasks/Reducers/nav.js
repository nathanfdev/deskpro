import { createReducer } from 'Ampliflux';
import * as NavActions from '../Actions/navActions';

const initialState = {
  allTasksCount: 0,
  myTasksCount: 0,
  teamTasksCount: 0,
  deptTasksCount: 0,
  delegatedTasksCount: 0,
  unassignedTasksCount: 0
};

export default createReducer(initialState, {
  [NavActions.loadAllTasksRemainingCount]: (state, payload) => {
    return state.set('allTasksCount', payload || 0);
  },
  [NavActions.loadMyTasksRemainingCount]: (state, payload) => {
    return state.set('myTasksCount', payload || 0);
  },
  [NavActions.loadTeamTasksRemainingCount]: (state, payload) => {
    return state.set('teamTasksCount', payload || 0);
  },
  [NavActions.loadDepartmentTasksRemainingCount]: (state, payload) => {
    return state.set('deptTasksCount', payload || 0);
  },
  [NavActions.loadDelegatedTasksRemainingCount]: (state, payload) => {
    return state.set('delegatedTasksCount', payload || 0);
  },
  [NavActions.loadUnassignedTasksRemainingCount]: (state, payload) => {
    return state.set('unassignedTasksCount', payload || 0);
  }
});
