import { createSelector } from 'reselect';

const chatListStateSelector = state => state.Chat.list;

export const sortingDataSelector = createSelector(
  chatListStateSelector,
  list => ({sort: list.get('sort'), order: list.get('order')})
);

export const currentListParamsSelector = createSelector(
  chatListStateSelector,
  list => list.get('currentListParams')
);