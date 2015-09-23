import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.list;

export const currentSortOptionSelector = createSelector(
  stateSelector,
  list => list.get('sortOptions').toJS().find(option => option.current === true)
);

export const sortingDataSelector = createSelector(
  [stateSelector, currentSortOptionSelector],
  (list, sortOption) => ({sort: sortOption.field, order: list.get('order')})
);

export const currentListParamsSelector = createSelector(
  stateSelector,
  list => list.get('currentListParams')
);

export const currentViewModeOptionSelector = createSelector(
  stateSelector,
  list => list.get('viewModeOptions').toJS().find(option => option.current === true)
);

export const viewModeSelector = createSelector(
  currentViewModeOptionSelector,
  option => option.field
);

export const elementsSelector = createSelector(
  stateSelector,
  list => list.get('elements')
);
