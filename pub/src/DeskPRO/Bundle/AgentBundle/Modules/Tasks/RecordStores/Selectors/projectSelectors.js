import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function appProjectStateSel(state) {
  return state.RecordStores.Tasks.projects;
}

export const projectStateSelector = createStoreSelectors(appProjectStateSel);
export const createProjectRequestSelectors = createRequestSelectorsBuilder(projectStateSelector);
export const allProjectsSelector = createSelector(
  createProjectRequestSelectors('all').recordsSel,
  projects => projects
);
