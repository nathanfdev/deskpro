import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/bootstrapActions';
import { setValue, setFullPayload, async } from 'Ampliflux/reducers/handlers';

const initialState = {
  sessionCode: localStorage.getItem('dpWidget.sessionCode'),
  loaded: false
};

export default createReducer(initialState, {
  [actions.setSessionCode]: setFullPayload('sessionCode'),
  [actions.bootstrapWidget]: async({
    done: setValue('loaded', true)
  })
});
