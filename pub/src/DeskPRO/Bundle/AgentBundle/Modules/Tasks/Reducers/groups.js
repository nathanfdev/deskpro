import { createReducer } from 'Ampliflux';
import * as TaskActions from '../Actions/tasksActions';

const initialState = {
  allTasksCount: 0,
  myTasksCount: 0,
  teamTasksCount: 0,
  deptTasksCount: 0,
  delegatedTasksCount: 0,
  unassignedTasksCount: 0
};

export default createReducer(initialState, {
  [TaskActions.loadAllTasksRemainingCount]: (state, payload) => {
    return state.set('allTasksCount', payload.data && payload.data.count || 0);
  },
  [TaskActions.loadMyTasksRemainingCount]: (state, payload) => {
    return state.set('myTasksCount', payload.data && payload.data.count || 0);
  },
  [TaskActions.loadTeamTasksRemainingCount]: (state, payload) => {
    return state.set('teamTasksCount', payload.data && payload.data.count || 0);
  },
  [TaskActions.loadDepartmentTasksRemainingCount]: (state, payload) => {
    return state.set('deptTasksCount', payload.data && payload.data.count || 0);
  },
  [TaskActions.loadDelegatedTasksRemainingCount]: (state, payload) => {
    return state.set('delegatedTasksCount', payload.data && payload.data.count || 0);
  },
  [TaskActions.loadUnassignedTasksRemainingCount]: (state, payload) => {
    return state.set('unassignedTasksCount', payload.data && payload.data.count || 0);
  }
});
