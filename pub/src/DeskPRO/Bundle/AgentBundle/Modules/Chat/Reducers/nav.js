import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatNavActions';

const initialState = {
  lists: {
    my: {
      total: 0,
      groupBy: 'date_period',
      isGroupingControlVisible: false,
      items: [/* {count, group} */]
    },
    all: {
      total: 0,
      groupBy: 'agent',
      isGroupingControlVisible: false,
      items: [/* {count, group} */]
    }
  }
};

export default createReducer(initialState, {

  [actions.loadCounts]: async({
    success: (state, payload) =>
      state.setIn(['lists', payload.list, 'total'], payload.counts.count)
           .setIn(['lists', payload.list, 'items'], payload.counts.nested)
  }),

  [actions.toggleListGroupingVisibility]: (state, payload) => {
    const target = ['lists', payload, 'isGroupingControlVisible'];
    return state.setIn(target, !state.getIn(target));
  },

  [actions.changeListGrouping]: (state, payload) => state.setIn(['lists', payload.list, 'groupBy'], payload.groupBy)

});
