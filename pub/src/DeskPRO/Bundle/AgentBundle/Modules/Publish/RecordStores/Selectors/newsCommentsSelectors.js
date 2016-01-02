import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function newsCommentsStateSel(state) {
  return state.RecordStores.Publish.newsComments;
}

export const newsCommentsStateSelector = createStoreSelectors(newsCommentsStateSel);
export const createNewsCommentsRequestSelectors = createRequestSelectorsBuilder(newsCommentsStateSelector);
