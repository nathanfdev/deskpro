import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  taskCount: 0,
  myTaskCount: 0,
  teamTaskCount: 0,
  deptTaskCount: 0,
  delegatedTaskCount: 0,
  unassignedTaskCount: 0
};

export default createReducer(initialState, {
  [TaskListActions.loadTasks]: (state, payload) => {
    const taskCount = payload.meta ? payload.data.count : 0;
    return state.set('taskCount', taskCount);
  },
  [TaskListActions.loadMyTasks]: (state, payload) => {
    const taskCount = payload.meta ? payload.data.count : 0;
    return state.set('myTaskCount', taskCount);
  },
  [TaskListActions.loadTeamTasks]: (state, payload) => {
    const taskCount = payload.meta ? payload.data.count : 0;
    return state.set('teamTaskCount', taskCount);
  },
  [TaskListActions.loadDepartmentTasks]: (state, payload) => {
    const taskCount = payload.meta ? payload.data.count : 0;
    return state.set('deptTaskCount', taskCount);
  },
  [TaskListActions.loadDelegatedTasks]: (state, payload) => {
    const taskCount = payload.meta ? payload.data.count : 0;
    return state.set('delegatedTaskCount', taskCount);
  },
  [TaskListActions.loadUnassignedTasks]: (state, payload) => {
    const taskCount = payload.meta ? payload.data.count : 0;
    return state.set('unassignedTaskCount', taskCount);
  }
});
