import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.list;

export const sortingDataSelector = createSelector(
  stateSelector,
    list => list.get('sortOptions').toJS().find(option => option.current === true)
);

export const currentListParamsSelector = createSelector(
  stateSelector,
  list => list.get('currentListParams')
);

export const viewDataSelector = createSelector(
  stateSelector,
  list => list.get('viewModeOptions').toJS().find(option => option.current === true)
);

export const elementsSelector = createSelector(
  stateSelector,
  list => list.get('elements')
);
