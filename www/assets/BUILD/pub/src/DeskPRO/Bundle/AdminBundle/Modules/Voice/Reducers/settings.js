import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/settingActions';

const initialState = {
  settingsLoaded: false,
  settings:       {}
};

export default createReducer(initialState, {
  [actions.loadSettings]: async({
    start:   state => state.set('settingsLoaded', false),
    success: setFullPayload('settings'),
    done:    state => state.set('settingsLoaded', true)
  })
});
