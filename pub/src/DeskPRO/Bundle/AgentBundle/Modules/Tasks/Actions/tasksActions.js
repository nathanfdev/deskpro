import { createAction } from 'Ampliflux';

export const setListParams = createAction('TASKS_SET_LIST_PARAMS');
export const loadList = createAction(
  'TASKS_LOAD_TASK_LIST',
  params => () => {
    console.log('loading tasks by params', params);
  }
);

export const applyListParams = createAction(
  'TASKS_APPLY_LIST_PARAMS',
    params => dispatch => {
      dispatch(setListParams(params));
      dispatch(loadList(params));
    }
);
