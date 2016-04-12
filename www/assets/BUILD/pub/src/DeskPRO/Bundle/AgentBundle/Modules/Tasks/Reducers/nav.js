import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/navActions';
import { async, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';

const initialState = {
  groups:   [],
  agents:   [],
  projects: [],

  async: {
    done: false
  }
};

export default createReducer(initialState, {
  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  })
});
