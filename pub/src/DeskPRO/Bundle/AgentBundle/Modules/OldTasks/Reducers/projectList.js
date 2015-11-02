import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  projectList: {},
};

export default createReducer(initialState, {
  [TaskListActions.loadProjects]: (state, payload) => {
    return state.set('projectList', payload.data);
  }
});
