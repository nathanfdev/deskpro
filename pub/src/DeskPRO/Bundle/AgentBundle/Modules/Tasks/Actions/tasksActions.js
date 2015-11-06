import { createAction } from 'Ampliflux';

export const applyListParams = createAction(
  'TASKS_APPLY_LIST_PARAMS',
  filter => filter
);
