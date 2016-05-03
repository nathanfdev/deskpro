import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/bootstrapActions';
import { setValue, setFullPayload, async } from 'Ampliflux/reducers/handlers';

const initialState = {
  session:  {},
  settings: {
    global: {
      chat:    {},
      company: {}
    },
    brand: {
      button: {},
      chat:   {},
      ticket: {},
      widget: {}
    }
  },
  loaded: false
};

export default createReducer(initialState, {
  [actions.getSession]: async({
    success: setFullPayload('session')
  }),
  [actions.setSettings]:     setFullPayload('settings'),
  [actions.bootstrapWidget]: async({
    done: setValue('loaded', true)
  })
});
