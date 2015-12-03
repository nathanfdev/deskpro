import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

// Audio notifications
export const audioNotificationsSelector = createSelector(
  stateSelector,
  state => state.get('audioNotifications')
);

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
  chatInfo => chatInfo.get('agent_id')
);

export const agentNameSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('agent_name')
);

export const agentAvatarSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('agent_avatar')
);

export const departmentNameSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('department_name')
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
  messages => messages && messages.size ? messages.max((a, b) => a.get('id') - b.get('id')).get('id') : null
);
