import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function chatsStateSel(state) {
  return state.RecordStores.Chat.chats;
}

export const chatsStateSelector = createStoreSelectors(chatsStateSel);
export const createChatsRequestSelectors = createRequestSelectorsBuilder(chatsStateSelector);
