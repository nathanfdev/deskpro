import { createReducer } from 'Ampliflux';
import * as TasksActions from '../Actions/tasksActions';

const initialState = {
  filter: null
};

export default createReducer(initialState, {
  [TasksActions.applyListParams]: (state, payload) => {
    return state.set('filter', payload);
  }
});
