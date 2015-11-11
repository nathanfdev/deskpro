import { createReducer } from 'Ampliflux';
import * as NavActions from '../Actions/navActions';
import { async, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';

const initialState = {
  groups: [],
  agents: [],
  projects: [],

  async: {
    done: false
  }
};

export default createReducer(initialState, {
  [NavActions.initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
