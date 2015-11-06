import { createReducer } from 'Ampliflux';
import * as TasksActions from '../Actions/tasksActions';

const initialState = {
  navItem: null
};

export default createReducer(initialState, {
  [TasksActions.applyListParams]: (state, payload) => {
    return state.set('navItem', payload);
  }
});
