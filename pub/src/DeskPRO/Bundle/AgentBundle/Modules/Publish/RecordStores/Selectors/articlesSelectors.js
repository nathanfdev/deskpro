import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function articlesStateSel(state) {
  return state.RecordStores.Publish.articles;
}

export const articlesStateSelector = createStoreSelectors(articlesStateSel);
export const createArticlesRequestSelectors = createRequestSelectorsBuilder(articlesStateSelector);
