import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  createdTask: null,
  failedTask: null
};

export default createReducer(initialState, {
  [TaskListActions.createTask]: (state, payload) => {
    return state.set('createdTask', payload);
  },
  [TaskListActions.failedTask]: (state, payload) => {
    return state.set('failedTask', payload);
  }
});
