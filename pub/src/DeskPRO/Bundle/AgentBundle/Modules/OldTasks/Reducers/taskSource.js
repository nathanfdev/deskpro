import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  taskSource: null
};

export default createReducer(initialState, {
  [TaskListActions.setSource]: (state, payload) => {
    return state.set('taskSource', payload);
  }
});
