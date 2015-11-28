import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

export const chatInfoSelector = createSelector(
  stateSelector,
  state => state.get('chat')
);
