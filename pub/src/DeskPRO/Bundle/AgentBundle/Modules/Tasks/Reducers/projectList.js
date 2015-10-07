import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  projects: {
    projectCount: 0,
    projectList: [],
  }
};

export default createReducer(initialState, {
  [TaskListActions.loadProjects]: (state, payload) => {
    const result = {
      projectList: payload.data,
      projectCount: payload.data ? payload.data.length : 0
    };

    return state.setIn(['projects'], result);
  }
});
