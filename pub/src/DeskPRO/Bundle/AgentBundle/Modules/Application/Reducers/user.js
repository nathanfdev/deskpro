import * as actions from '../Actions/AppActions';
import { createReducer } from 'Ampliflux';

const initialState = {
  id: null
};

export default createReducer(initialState, {
  [actions.setAppUser]: (state, payload) => {
    return state.merge(payload.person);
  }
});
