import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function articlePendingCreatesStateSel(state) {
  return state.RecordStores.Publish.articlePendingCreates;
}

export const articlePendingCreatesStateSelector = createStoreSelectors(articlePendingCreatesStateSel);
export const createArticlePendingCreatesRequestSelectors = createRequestSelectorsBuilder(articlePendingCreatesStateSelector);
