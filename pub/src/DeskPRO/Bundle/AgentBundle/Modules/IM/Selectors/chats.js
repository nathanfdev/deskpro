import { createSelector } from 'reselect';
import { createChatsRequestSelectors } from '../RecordStores/Selectors/chats';

export const chatsSelector = createSelector(
  createChatsRequestSelectors('chats').recordsSel,
    chats => { return chats.toJS(); }
);
