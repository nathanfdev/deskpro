import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';
import { listFilterSelector, currentSortSelector, currentOrderSelector } from '../Selectors/list';

export const setListParamsFilter = createAction('TASKS_SET_LIST_PARAMS_FILTER');

export const toggleTableFieldVisibility = createAction('TASKS_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TASKS_TOGGLE_CARD_FIELD_VISIBILITY');
export const toggleKanbanFieldVisibility = createAction('TASKS_TOGGLE_KANBAN_FIELD_VISIBILITY');
export const toggleCalendarFieldVisibility = createAction('TASKS_TOGGLE_CALENDAR_FIELD_VISIBILITY');

export const loadList = createAction(
  'TASKS_LOAD_TASK_LIST',
  () => (dispatch, getState) => new Promise(resolve => {
    const state = getState();
    const listParams = listFilterSelector(state).toJS();
    const params = {
      ...listParams,
      sort: currentSortSelector(state),
      order: currentOrderSelector(state)
    };

    return DpApi
      .sendGet('DP_API/tasks?' + Tasks.compileParams(params))
      .success(response => resolve(response.data));
  })
);
