import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function appMessagesStateSel(state) {
  return state.IM.messages;
}

export const messagesStateSelector = createStoreSelectors(appMessagesStateSel);

export const createMessagesRequestSelectors = createRequestSelectorsBuilder(messagesStateSelector);