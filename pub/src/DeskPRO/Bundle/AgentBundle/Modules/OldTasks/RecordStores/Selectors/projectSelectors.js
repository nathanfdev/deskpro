import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

// You need to define this top-level
// selector that fetches record-store state
// from the global app state
function appProjectStateSel(state) {
  return state.RecordStores.OldTasks.projects;
}

// This creates a number of selectors for records, status and requests
// This is not typically used by client-code (usually only used by the next builder)
export const projectStateSelector = createStoreSelectors(appProjectStateSel);

// This create a number of useful selectors that your client-code will find useful
export const createProjectRequestSelectors = createRequestSelectorsBuilder(projectStateSelector);

export const allProjectsSelector = createSelector(
  createProjectRequestSelectors('all').recordsSel,
  projects => projects
);
