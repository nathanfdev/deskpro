import { createAction } from "Ampliflux/actions";

export const loadChatsList = createAction(
  'CHATS_LOAD_LIST',
  trigger => trigger(['chat1', 'chat2', 'chat3'])
)
