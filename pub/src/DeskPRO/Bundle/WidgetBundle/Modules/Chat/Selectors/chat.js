import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

export const chatInfoSelector = createSelector(
  stateSelector,
  state => state.get('chat')
);

export const chatIdSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('conversation_id')
);

export const agentIdSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('agent_id')
);

export const messagesSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('messages')
);
