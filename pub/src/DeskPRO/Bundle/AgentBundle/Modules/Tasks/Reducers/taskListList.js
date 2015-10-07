import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  taskList: []
};

export default createReducer(initialState, {
  [TaskListActions.loadLists]: (state, payload) => {
    return state.set('taskList', payload.data);
  }
});
