import { createSelector } from 'reselect';
import { createChatsRequestSelectors } from '../RecordStores/Selectors/chats';

export const chatsSelector = createSelector(
  createChatsRequestSelectors('chats').recordsSel,
    chats => { console.log(chats), chats.toJS(); }
);
