import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.chat;

// Phrase translations
export const phraseTranslationsSelector = createSelector(
  stateSelector,
    state => state.get('phrases')
);

// Audio selectors
export const muteSelector = createSelector(
  stateSelector,
  state => state.get('mute')
);

// Transcript selectors
export const transcriptCheckedSelector = createSelector(
  stateSelector,
  state => state.getIn(['transcript', 'checked'])
);

export const transcriptSavingSelector = createSelector(
  stateSelector,
    state => state.getIn(['transcript', 'saving'])
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

export const chatLoadedSelector = createSelector(
  stateSelector,
  state => state.get('chatLoaded')
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
  chatInfo => chatInfo.get('agent_name') || 'Agent'
);

export const agentAvatarSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('agent_avatar')
);

export const agentTypingDateSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('date_agent_typing')
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

export const messageIdsSelector = createSelector(
  messagesSelector,
  messages => messages.map(message => message.get('id')).filter(id => id !== null)
);

export const lastMessageIdSelector = createSelector(
  messageIdsSelector,
  messageIds => messageIds.size ? messageIds.max((a, b) => a - b) : null
);

// Uploading files selectors
export const uploadingFilesSelector = createSelector(
  stateSelector,
  state => state.getIn(['uploading', 'files'])
);

export const uploadingFilesFailedSelector = createSelector(
  stateSelector,
  state => state.getIn(['uploading', 'failed'])
);

export const uploadingFilesRepeatSelector = createSelector(
  stateSelector,
  state => state.getIn(['uploading', 'repeat'])
);

// Attachments selectors
export const attachmentsSelector = createSelector(
  stateSelector,
  state => state.get('attachments')
);

export const attachedImagesSelector = createSelector(
  attachmentsSelector,
  attachments => attachments.filter(attachment => attachment.get('is_image'))
);

export const attachedImagesCountSelector = createSelector(
  attachedImagesSelector,
  attachments => attachments.size
);

export const attachedFilesSelector = createSelector(
  attachmentsSelector,
  attachments => attachments.filter(attachment => !attachment.get('is_image'))
);
