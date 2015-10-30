import { createSelector } from 'reselect';
import { mapKeyedFromArray, toPropsMap, reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';
import Immutable from 'immutable';

const stateSelector = state => state.NewTickets.nav;

export const filterSetsSelector = createSelector(stateSelector, state => state.get('filterSets'));

export const filterSetsCountSelector = createSelector(
  stateSelector,
  state => mapKeyedFromArray(state.get('filterSetsCount'), 'group')
);

const filtersSelector = createSelector(stateSelector, state => state.get('filters'));
export const filterNamesSelector = createSelector(filtersSelector, filters => toPropsMap('id', 'title', filters));

export const editedFilterIdSelector = createSelector(stateSelector, state => state.get('editedFilterId'));
export const editedFilterSelector = createSelector(
  [editedFilterIdSelector, filtersSelector],
  (id, filters) => filters.find(filter => filter.get('id') === id)
);

export const labelsSelector = createSelector(
  stateSelector,
  state => reduceImmutableToProperty('label', Immutable.fromJS(state.get('labels')))
);

const starsSelector = createSelector(stateSelector, state => state.get('stars'));
export const starNamesSelector = createSelector(starsSelector, stars => toPropsMap('id', 'name', Immutable.fromJS(stars)));

export const starsCountSelector = createSelector(
  stateSelector,
  state => Immutable.fromJS(state.get('starsCount'))
);

export const isDoneSelector = function(request) {
  return createSelector(
    stateSelector,
    state => state.getIn(['done', request])
  );
};

export const loadingFilterIdsSelector = createSelector(
  stateSelector,
  state => state.get('filtersLoading')
);