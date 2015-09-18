import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.list;

export const sortingDataSelector = createSelector(
  stateSelector,
    list => list.get('sortOptions').toJS().find(option => option.current === true).field
);
