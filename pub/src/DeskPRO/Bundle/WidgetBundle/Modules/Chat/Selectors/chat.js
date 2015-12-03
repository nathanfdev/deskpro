import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

// Chat info selectors
export const chatInfoSelector = createSelector(
  stateSelector,
  state => state.get('chatInfo')
);

export const chatIdSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('id')
);

export const agentIdSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('agent')
);

export const dateEndedSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('date_ended')
);

export const isEndedSelector = createSelector(
  dateEndedSelector,
  dateEnded => !!dateEnded
);

// Messages selectors
export const messagesSelector = createSelector(
  stateSelector,
  chatInfo => chatInfo.get('messages')
);

export const lastMessageIdSelector = createSelector(
  messagesSelector,
  messages => messages && messages.size ? messages.max((a, b) => a.get('id') - b.get('id')).first() : null
);
