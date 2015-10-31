import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  taskFilter: null
};

export default createReducer(initialState, {
  [TaskListActions.setFilter]: (state, payload) => {
    return state.set('taskFilter', payload);
  }
});
