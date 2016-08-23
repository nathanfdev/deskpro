import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { pauseOnboarding } from '../../Onboarding/Actions/onboardingActions';

const initialState = {};

export default createReducer(initialState, {
  [pauseOnboarding]: (state, payload) => {
    console.log('onboarding Pause');
    console.log(state);
    console.log(payload);
    return state;
  }
});
