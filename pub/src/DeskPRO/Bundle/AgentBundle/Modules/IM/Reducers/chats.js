import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatsActions';

const initialState = {
  current: {},
};

export default createReducer(initialState, {
  [actions.startChat]: (state, payload) => {
    return state.set('current', payload);
  }
});
