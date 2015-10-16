import * as actions from '../Actions/loginActions';
import { createReducer } from 'Ampliflux';

const initialState = {
  email: 'example@email.com',
  emailError: null,
  password: 'password',
  passwordError: null,
  rememberMe: true
};

export default createReducer(initialState, {
  [actions.emailChange]: (state, payload) => {
    return state.set('email', payload);
  },
  [actions.emailSetError]: (state, payload) => {
    return state.set('emailError', payload);
  },
  [actions.passwordChange]: (state, payload) => {
    return state.set('password', payload);
  },
  [actions.passwordSetError]: (state, payload) => {
    return state.set('passwordError', payload);
  },
  [actions.toggleRememberMe]: state => {
    return state.set('rememberMe', !state.get('rememberMe'));
  }
});
