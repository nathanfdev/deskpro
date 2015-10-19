import { createAction } from 'Ampliflux';

export const toggleOverlay = createAction('IM_TOGGLE_OVERLAY');

export const openChat = createAction(
  'IM_OPEN_CHAT',
  (targetId, targetType = 'agent') => {
    return {id: targetId, chat_type: targetType};
  }
);

export const closeChat = createAction('IM_CLOSE_CHAT');
