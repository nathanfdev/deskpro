import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

// You need to define this top-level
// selector that fetches record-store state
// from the global app state
function appLinkedItemStateSel(state) {
  return state.RecordStores.OldTasks.linkedItems;
}

// This creates a number of selectors for records, status and requests
// This is not typically used by client-code (usually only used by the next builder)
export const linkedItemStateSelector = createStoreSelectors(appLinkedItemStateSel);

// This create a number of useful selectors that your client-code will find useful
export const createLinkedItemRequestSelectors = createRequestSelectorsBuilder(linkedItemStateSelector);

export const allLinkedItemsSelector = createSelector(
  createLinkedItemRequestSelectors('all').recordsSel,
  linkedItems => linkedItems
);
