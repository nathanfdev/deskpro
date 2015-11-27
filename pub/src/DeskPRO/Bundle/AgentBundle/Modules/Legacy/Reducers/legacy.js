import { createReducer } from 'Ampliflux';
import * as legacyActions from '../Actions/legacyActions';
import { doneInitialLoad } from '../../Application/Actions/appActions';
const initialState = {};

export default createReducer(initialState, {
  [doneInitialLoad]: (state) => {
    // side effects!
    window.setupLegacy();
    return state;
  },

  [legacyActions.loadRoute]: (state, payload) => {
    window.DeskPRO_Window.runPageRoute(payload.type + ':' + DP_BASE_URL_RELATIVE + '/old-agent' + payload.path);
    return state;
  }
});
