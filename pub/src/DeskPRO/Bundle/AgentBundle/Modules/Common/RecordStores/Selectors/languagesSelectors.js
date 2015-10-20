import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function languagesStateSel(state) {
  return state.RecordStores.Common.languages;
}

export const languagesStateSelector = createStoreSelectors(languagesStateSel);
export const createLanguagesRequestSelectors = createRequestSelectorsBuilder(languagesStateSelector);
