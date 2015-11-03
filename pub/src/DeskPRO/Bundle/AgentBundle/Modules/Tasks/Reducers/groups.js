import { createReducer } from 'Ampliflux';
import * as GroupsActions from '../Actions/groupsActions';

const initialState = {
  allTasksCount: 0,
  myTasksCount: 0,
  teamTasksCount: 0,
  deptTasksCount: 0,
  delegatedTasksCount: 0,
  unassignedTasksCount: 0
};

export default createReducer(initialState, {
  [GroupsActions.loadAllTasksRemainingCount]: (state, payload) => {
    return state.set('allTasksCount', payload || 0);
  },
  [GroupsActions.loadMyTasksRemainingCount]: (state, payload) => {
    return state.set('myTasksCount', payload || 0);
  },
  [GroupsActions.loadTeamTasksRemainingCount]: (state, payload) => {
    return state.set('teamTasksCount', payload || 0);
  },
  [GroupsActions.loadDepartmentTasksRemainingCount]: (state, payload) => {
    return state.set('deptTasksCount', payload || 0);
  },
  [GroupsActions.loadDelegatedTasksRemainingCount]: (state, payload) => {
    return state.set('delegatedTasksCount', payload || 0);
  },
  [GroupsActions.loadUnassignedTasksRemainingCount]: (state, payload) => {
    return state.set('unassignedTasksCount', payload || 0);
  }
});
