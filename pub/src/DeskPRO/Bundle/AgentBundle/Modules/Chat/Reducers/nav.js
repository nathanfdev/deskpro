import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatNavActions';

const initialState = {
  async: {
    done: false
  },
  my: {
    total: 0,
    grouped_by: 'date_period',
    isGroupingControlVisible: false,
    nested: [/* {count, group} */]
  },
  all: {
    total: 0,
    grouped_by: 'agent',
    isGroupingControlVisible: false,
    nested: [/* {count, group} */]
  }
};

export default createReducer(initialState, {

  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),

  [actions.loadCounts]: async({
    success: (state, payload) =>
      state.set(payload.list, Immutable.fromJS(payload.counts))
  }),

  [actions.toggleListGroupingVisibility]: (state, payload) => {
    const target = ['lists', payload, 'isGroupingControlVisible'];
    return state.setIn(target, !state.getIn(target));
  },

  [actions.changeListGrouping]: (state, payload) => state.setIn([payload.list, 'grouped_by'], payload.groupBy)

});
