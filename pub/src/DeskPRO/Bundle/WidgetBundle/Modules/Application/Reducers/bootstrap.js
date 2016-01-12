import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/bootstrapActions';
import { setValue, setFullPayload, async } from 'Ampliflux/reducers/handlers';

const initialState = {
  sessionCode: null,
  settings: {
    chat: {}
  },
  loaded: false
};

export default createReducer(initialState, {
  [actions.getSession]: async({
    success: setFullPayload('sessionCode')
  }),
  [actions.getSettings]: async({
    success: setFullPayload('settings')
  }),
  [actions.bootstrapWidget]: async({
    done: setValue('loaded', true)
  })
});
