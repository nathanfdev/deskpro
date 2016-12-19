import { createReducer } from 'Ampliflux';
import { async, setValue, mergeFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/crmNavActions';

export const crmNavInitialState = {
  async:              { done: false },
  organizations:      { total: 0 },
  users:              { total: 0, groups: [/* {count, group} */] },
  agents:             { total: 0, teams: [/* {count, group} */] },
  organizationLabels: [],
  personLabels:       []
};

export default createReducer(crmNavInitialState, {
  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  })
});
