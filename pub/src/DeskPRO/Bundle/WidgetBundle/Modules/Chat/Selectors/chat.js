import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

// Controls selectors
export const audioNotificationsSelector = createSelector(
  stateSelector,
  state => state.get('audioNotifications')
);

export const transcriptCheckedSelector = createSelector(
  stateSelector,
  state => state.getIn(['transcript', 'checked'])
);

export const transcriptSendingSelector = createSelector(
  stateSelector,
  state => state.getIn(['transcript', 'sending'])
);

export const transcriptSentSelector = createSelector(
  stateSelector,
  state => state.getIn(['transcript', 'sent'])
);

// Chat info selectors
export const chatIdSelector = createSelector(
  stateSelector,
  state => state.get('chatId')
);

export const chatInfoSelector = createSelector(
  stateSelector,
  state => state.get('chatInfo')
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

export const authorEmailSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('author_email')
);

export const authorNameSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('author_name')
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
  state => state.get('messages')
);

export const lastMessageIdSelector = createSelector(
  messagesSelector,
  messages => messages && messages.size ? messages.map(message => message.get('id')).max((a, b) => a - b) : null
);

export const attachmentsSelector = createSelector(
  stateSelector,
  state => state.get('attachments')
);
