import { createSelector } from 'reselect';
import { createChatsRequestSelectors } from '../RecordStores/Selectors/chatsSelectors';

export const chatsSelector = createSelector(
  createChatsRequestSelectors('chats').recordsSel,
    chats => chats
);
