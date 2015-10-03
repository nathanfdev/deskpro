import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function appChatsStateSel(state) {
  return state.RecordStores.chats.chats;
}

// This creates a number of selectors for records, status and requests
// This is not typically used by client-code (usually only used by the next builder)
export const chatsStateSelector = createStoreSelectors(appChatsStateSel);

// This create a number of useful selectors that your client-code will find useful
export const createChatsRequestSelectors = createRequestSelectorsBuilder(chatsStateSelector);