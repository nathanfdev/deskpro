import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatsActions';
import Immutable from 'immutable';

const initialState = {
  current: {target_id: 0, target_type: 'agent'}
};

export default createReducer(initialState, {
  [actions.startChat]: (state, payload) => {
    return state.set('current', payload);
  }
});