import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function chatsStateSel(state) {
  return state.RecordStores.IM.chats;
}

export const chatsStateSelector = createStoreSelectors(chatsStateSel);
export const createChatsRequestSelectors = createRequestSelectorsBuilder(chatsStateSelector);

export const chatsSelector = createSelector(
  createChatsRequestSelectors('all').recordsSel,
  chats => chats
);

export const recentChatsSelector = createSelector(
  createChatsRequestSelectors('recent').recordsSel,
  chats => chats
);

export const recentChatsStatusSelector = createSelector(
  createChatsRequestSelectors('recent').statusSel,
  chats => chats
);
