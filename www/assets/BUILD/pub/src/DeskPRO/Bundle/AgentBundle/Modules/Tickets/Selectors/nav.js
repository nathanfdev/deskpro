import { createSelector } from 'reselect';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

const stateSelector = state => state.Tickets.nav;

export const filterSetsCountSelector = createSelector(
  stateSelector,
  state => mapKeyedFromArray(state.get('filterSetsCount'), 'id')
);

const filtersSelector               = createSelector(stateSelector, state => state.get('filters'));
export const editedFilterIdSelector = createSelector(stateSelector, state => state.get('editedFilterId'));
export const editedFilterSelector   = createSelector(
  [editedFilterIdSelector, filtersSelector],
  (id, filters) => filters.find(filter => filter.get('id') === id)
);

export const labelsSelector = createSelector(
  stateSelector,
  state => state.get('labels')
);

export const starsCountSelector = createSelector(
  stateSelector,
  state => state.get('starsCount')
);

export const isLoadedSelector = createSelector(
  stateSelector,
  state => state.getIn(['async', 'done'])
);

export const loadingFilterIdsSelector = createSelector(
  stateSelector,
  state => state.getIn(['async', 'filtersLoading'])
);
