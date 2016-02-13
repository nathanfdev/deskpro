import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function appTaskLabelStateSel(state) {
  return state.RecordStores.Tasks.taskLabels;
}

export const taskLabelStateSelector = createStoreSelectors(appTaskLabelStateSel);
export const createTaskLabelRequestSelectors = createRequestSelectorsBuilder(taskLabelStateSelector);
export const allTaskLabelsSelector = createSelector(
  createTaskLabelRequestSelectors('all').recordsSel,
  taskLabels => taskLabels
);
