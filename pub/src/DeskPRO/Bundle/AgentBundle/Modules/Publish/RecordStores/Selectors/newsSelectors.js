import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function newsStateSel(state) {
  return state.RecordStores.Publish.news;
}

export const newsStateSelector = createStoreSelectors(newsStateSel);
export const createNewsRequestSelectors = createRequestSelectorsBuilder(newsStateSelector);
