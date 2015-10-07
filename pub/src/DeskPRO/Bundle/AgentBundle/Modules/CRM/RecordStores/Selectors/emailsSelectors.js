import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function emailsStateSel(state) {
  return state.RecordStores.CRM.emails;
}

export const emailsStateSelector = createStoreSelectors(emailsStateSel);
export const createEmailsRequestSelectors = createRequestSelectorsBuilder(emailsStateSelector);
