import Immutable from 'immutable';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { allSelectorFactory, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const increaseCount = createAction(
  'INCREASE_FILTER_COUNT',
  id => (dispatch, getState) => {
    const state = getState();
    const filtersCounts = allSelectorFactory('TicketFilterCounts')(state);

    const count = filtersCounts.find(e => e.get('id') === id);
    const newCount = count.set('count', count.get('count', 0) + 1);
    dispatch(updateCollection('TicketFilterCounts', Immutable.List([newCount])));
  }
);

export const decreaseCount = createAction(
  'DECREASE_FILTER_COUNT',
  id => (dispatch, getState) => {
    const state = getState();
    const filtersCounts = allSelectorFactory('TicketFilterCounts')(state);

    const count = filtersCounts.find(e => e.get('id') === id);
    const newCount = count.set('count', count.get('count', 0) - 1);
    dispatch(updateCollection('TicketFilterCounts', Immutable.List([newCount])));
  }
);
