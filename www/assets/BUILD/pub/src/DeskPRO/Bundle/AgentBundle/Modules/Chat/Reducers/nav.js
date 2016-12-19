import Immutable from 'immutable';
import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, mergeFullPayload, setValue } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/navActions';

export const chatNavInitialState = {
  async:  { done: false },
  counts: []
};

export default createReducer(chatNavInitialState, {

  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  }),

  [actions.reloadCount]: async({
    success: (state, payload) => {
      const i = state.get('counts').findIndex(c => c.get('id') == payload.countId);
      const next = state
        .setIn(['counts', i, 'nested'], Immutable.fromJS(payload.count.nested))
        .setIn(['counts', i, 'grouped_by'], Immutable.fromJS(payload.count.grouped_by))
      ;

      return next;
    },
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  })
});
