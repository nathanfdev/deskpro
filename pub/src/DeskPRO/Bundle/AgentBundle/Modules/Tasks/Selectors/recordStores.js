import { createSelector } from 'reselect';
import { createTaskListRequestSelectors } from '../RecordStores/Selectors/taskListSelectors';

export const tasksSelector = createSelector(
  createTaskListRequestSelectors('tasks').recordsSel,
    feedback => feedback
);
