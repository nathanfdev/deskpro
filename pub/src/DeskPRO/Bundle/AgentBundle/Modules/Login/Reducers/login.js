import * as actions from '../Actions/loginActions';
import { createReducer } from 'Ampliflux';

const initialState = {
  emailError: null,
  passwordError: null,
  hasAuth: false
};

export default createReducer(initialState, {
  [actions.emailSetError]: (state, payload) => {
    return state.set('emailError', payload);
  },
  [actions.passwordSetError]: (state, payload) => {
    return state.set('passwordError', payload);
  },
  [actions.setHasAuth]: (state, payload) => {
    return state.set('hasAuth', payload);
  }
});
