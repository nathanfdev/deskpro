import { createReducer } from 'Ampliflux';
import * as TasksActions from '../Actions/tasksActions';

const initialState = {
  listParams: null
};

export default createReducer(initialState, {
  [TasksActions.setListParams]: (state, payload) => {
    return state.set('listParams', payload);
  }
});
