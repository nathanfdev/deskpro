import Immutable from 'immutable';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
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

export const loadGrouping = createAction(
  'FILTER_LOAD_GROUPING',
  (id, groupBy) => (dispatch, getState) => {
    const state = getState();
    const filtersCounts = allSelectorFactory('TicketFilterCounts')(state);

    let filterCount = filtersCounts.find(e => e.get('id') === id);
    if (!filterCount) {
      return null;
    }
    dispatch(updateCollection('TicketFilterCounts', Immutable.List([filterCount.set('nested', Immutable.fromJS([]))])));
    if (groupBy !== '@none') {
      const count = { endpoint: `ticket_filters2/${id}/count`, query: `group_by=ticket.${groupBy}` };

      api.sendGet(api.prepareParams({ count }))
        .success((countsResponses) => {
          const data = flattenBatchResponses(countsResponses.responses);
          if (data.count) {
            filterCount = filterCount.set('nested', Immutable.fromJS(data.count.nested));
            dispatch(updateCollection('TicketFilterCounts', Immutable.List([filterCount])));
          }
        });
    }
    return true;
  }
);
