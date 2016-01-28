import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function downloadsStateSel(state) {
  return state.RecordStores.Publish.downloads;
}

export const downloadsStateSelector = createStoreSelectors(downloadsStateSel);
export const createDownloadsRequestSelectors = createRequestSelectorsBuilder(downloadsStateSelector);
