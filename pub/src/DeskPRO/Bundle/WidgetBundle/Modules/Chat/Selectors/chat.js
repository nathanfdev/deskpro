import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

export const isCreated = createSelector(
  stateSelector,
  state => state.getIn(['async', 'createChat'])
);
