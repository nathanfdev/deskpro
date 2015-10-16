import * as actions from '../Actions/loginActions';
import { createReducer } from 'Ampliflux';

const initialState = {
  email: 'example@email.com',
  password: 'password',
  rememberMe: true
};

export default createReducer(initialState, {
  [actions.emailChange]: (state, payload) => {
    return state.set('email', payload);
  },
  [actions.passwordChange]: (state, payload) => {
    return state.set('password', payload);
  }
});
