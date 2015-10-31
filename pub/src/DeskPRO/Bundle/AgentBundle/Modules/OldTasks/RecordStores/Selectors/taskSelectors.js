import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

// You need to define this top-level
// selector that fetches record-store state
// from the global app state
function appTaskStateSel(state) {
  return state.RecordStores.OldTasks.tasks;
}

// This creates a number of selectors for records, status and requests
// This is not typically used by client-code (usually only used by the next builder)
export const taskStateSelector = createStoreSelectors(appTaskStateSel);

// This create a number of useful selectors that your client-code will find useful
export const createTaskRequestSelectors = createRequestSelectorsBuilder(taskStateSelector);

export const allTasksSelector = createSelector(
  createTaskRequestSelectors('all').recordsSel,
  tasks => tasks
);

export const statusFilteredTasksSelector = createSelector(
  createTaskRequestSelectors('loadTaskList').statusSel,
  tasks => tasks
);

export const filteredTasksSelector = createSelector(
  createTaskRequestSelectors('loadTaskList').recordsSel,
  tasks => tasks
);
