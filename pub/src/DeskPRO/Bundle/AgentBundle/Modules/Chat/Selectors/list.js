import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.list;

export const sortingDataSelector = createSelector(
  stateSelector,
  list => ({sort: list.get('sort'), order: list.get('order')})
);

export const currentListParamsSelector = createSelector(
  stateSelector,
  list => list.get('currentListParams')
);

export const viewModeSelector = createSelector(
  stateSelector,
  list => list.get('viewModeOptions').toJS().find(option => option.current === true).field
);

export const elementsSelector = createSelector(
  stateSelector,
  list => list.get('elements')
);
