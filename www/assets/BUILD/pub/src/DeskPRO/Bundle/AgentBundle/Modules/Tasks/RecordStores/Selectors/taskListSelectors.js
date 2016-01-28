import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function appTaskListStateSel(state) {
  return state.RecordStores.Tasks.taskLists;
}

export const taskListStateSelector = createStoreSelectors(appTaskListStateSel);
export const createTaskListRequestSelectors = createRequestSelectorsBuilder(taskListStateSelector);
export const allTaskListsSelector = createSelector(
  createTaskListRequestSelectors('all').recordsSel,
  taskLists => taskLists
);
