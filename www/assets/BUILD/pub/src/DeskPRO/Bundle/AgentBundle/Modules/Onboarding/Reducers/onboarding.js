import { createReducer } from 'DeskPRO/Component/Ampliflux';
import * as actions from '../Actions/onboardingActions';

const initialState = {
  logoCallback() {},
  logoActive: false
};

export default createReducer(initialState, {
  [actions.pauseOnboarding]: (state, payload) => {
    let newState = state;
    if (payload.callback) {
      newState = newState.set('logoCallback', payload.callback);
    }
    if (payload.active) {
      newState = newState.set('logoActive', payload.active);
    }
    return newState;
  },
  [actions.resumeOnboarding]: (state) => state.set('logoActive', false)
});
