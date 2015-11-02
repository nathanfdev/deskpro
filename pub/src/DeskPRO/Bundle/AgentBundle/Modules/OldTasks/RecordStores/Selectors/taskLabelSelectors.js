import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

// You need to define this top-level
// selector that fetches record-store state
// from the global app state
function appTaskLabelStateSel(state) {
  return state.RecordStores.OldTasks.taskLabels;
}

// This creates a number of selectors for records, status and requests
// This is not typically used by client-code (usually only used by the next builder)
export const taskLabelStateSelector = createStoreSelectors(appTaskLabelStateSel);

// This create a number of useful selectors that your client-code will find useful
export const createTaskLabelRequestSelectors = createRequestSelectorsBuilder(taskLabelStateSelector);

export const allTaskLabelsSelector = createSelector(
  createTaskLabelRequestSelectors('allTaskLabels').recordsSel,
  taskLabels => taskLabels
);
