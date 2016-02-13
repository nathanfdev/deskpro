import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function articlesCommentsStateSel(state) {
  return state.RecordStores.Publish.articlesComments;
}

export const articlesCommentsStateSelector = createStoreSelectors(articlesCommentsStateSel);
export const createArticlesCommentsRequestSelectors = createRequestSelectorsBuilder(articlesCommentsStateSelector);
