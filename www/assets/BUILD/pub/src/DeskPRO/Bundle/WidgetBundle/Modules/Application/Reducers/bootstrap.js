import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/bootstrapActions';
import { setValue, setFullPayload, async, composeHandlers } from 'Ampliflux/reducers/handlers';
import { dispatchWidgetStatus } from '../../../Services/WindowApi';

const initialState = {
  session:  {},
  settings: {
    chat:    {},
    company: {}
  },
  loaded: false
};

export default createReducer(initialState, {
  [actions.getSession]: async({
    success: setFullPayload('session')
  }),
  [actions.setSettings]:     setFullPayload('settings'),
  [actions.bootstrapWidget]: async({
    done: composeHandlers(
      setValue('loaded', true),
      state => {
        setTimeout(() => dispatchWidgetStatus(), 1);
        return state;
      }
    )
  })
});
