import { createReducer } from 'DeskPRO/Component/Ampliflux';
import * as actions from '../../Onboarding/Actions/onboardingActions';

const initialState = {
  logoCallback() {},
  logoActive: false
};

export default createReducer(initialState, {
  [actions.pauseOnboarding]: (state, payload) => {
    state.merge({
      logoCallback: payload.callback,
      logoActive:   true
    });
    return state;
  }
});
