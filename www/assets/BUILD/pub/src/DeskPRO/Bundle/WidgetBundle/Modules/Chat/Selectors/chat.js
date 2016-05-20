import { createSelector } from 'reselect';
import { peopleSelector } from '../../Application/Selectors/peopleSelectors';

const stateSelector = state => state.Chat.chat;

// Polling
export const lockedPollingSelector = createSelector(
  stateSelector,
  state => state.getIn(['polling', 'locked'])
);

export const skippedPollingSelector = createSelector(
  stateSelector,
  state => state.getIn(['polling', 'skipped'])
);

export const disabledPollingSelector = createSelector(
  lockedPollingSelector,
  skippedPollingSelector,
  (locked, skipped) => locked || skipped
);

// Feedback selectors
export const feedbackStageSelector = createSelector(
  stateSelector,
  state => state.get('feedbackStage')
);

// Audio selectors
export const muteSelector = createSelector(
  stateSelector,
  state => state.get('mute')
);

// Chat info selectors
export const chatIdSelector = createSelector(
  stateSelector,
  state => state.getIn(['chat', 'id'])
);

export const chatLoadedSelector = createSelector(
  stateSelector,
  state => state.getIn(['chat', 'loaded'])
);

export const canReopenSelector = createSelector(
  stateSelector,
  state => state.getIn(['chat', 'canReopen'])
);

export const chatInfoSelector = createSelector(
  stateSelector,
  state => state.getIn(['chat', 'info'])
);

export const hasChatInfoSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo && chatInfo.size > 0
);

export const agentIdSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('agent')
);

export const agentSelector = createSelector(
  agentIdSelector,
  peopleSelector,
  (agentId, people) => people.get(agentId)
);

export const agentNameSelector = createSelector(
  agentSelector,
  agent => agent && agent.get('display_name') || 'Agent'
);

export const agentAvatarSelector = createSelector(
  agentSelector,
  agent => agent && agent.get('avatar')
);

export const agentTypingDateSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('date_agent_typing')
);

export const departmentNameSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('department_name')
);

export const authorIdSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('person')
);

export const authorSelector = createSelector(
  authorIdSelector,
  peopleSelector,
  (authorId, people) => people && people.get(authorId)
);

export const authorEmailSelector = createSelector(
  chatInfoSelector,
  authorSelector,
  (chatInfo, author) => chatInfo.get('person_email') || author && author.get('primary_email_address')
);

export const authorNameSelector = createSelector(
  chatInfoSelector,
  authorSelector,
  (chatInfo, author) => chatInfo.get('person_name') || author && author.get('display_name') || 'User'
);

export const dateEndedSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('date_ended')
);

export const isEndedSelector = createSelector(
  dateEndedSelector,
  dateEnded => !!dateEnded
);

export const needValidateEmailSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('need_validate_email')
);

export const transcriptCheckedSelector = createSelector(
  chatInfoSelector,
  chatInfo => chatInfo.get('should_send_transcript')
);

export const transcriptSentSelector = createSelector(
  chatInfoSelector,
  chatInfo => !!chatInfo.get('date_transcript_sent')
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
  messageIds => (messageIds.size ? messageIds.max((a, b) => a - b) : null)
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
