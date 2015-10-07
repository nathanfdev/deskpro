import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  createdProject: null,
  failedProject: null
};

export default createReducer(initialState, {
  [TaskListActions.createProject]: (state, payload) => {
    return state.set('createdProject', payload.data);
  },
  [TaskListActions.failedProject]: (state, payload) => {
    return state.set('failedProject', payload.data);
  }
});
