import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function downloadsCommentsStateSel(state) {
  return state.RecordStores.Publish.downloadsComments;
}

export const downloadsCommentsStateSelector = createStoreSelectors(downloadsCommentsStateSel);
export const createDownloadsCommentsRequestSelectors = createRequestSelectorsBuilder(downloadsCommentsStateSelector);
