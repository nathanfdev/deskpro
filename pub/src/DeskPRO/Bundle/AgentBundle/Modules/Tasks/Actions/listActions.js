import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';
import { listParamsNavSelector, listParamsFiltersSelector, currentSortSelector, currentOrderSelector } from '../Selectors/list';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';

export const setListParamsNav = createAction('TASKS_SET_LIST_PARAMS_NAV');
export const setListParamsFilters = createAction('TASKS_SET_LIST_PARAMS_FILTERS');

export const toggleTableFieldVisibility = createAction('TASKS_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TASKS_TOGGLE_CARD_FIELD_VISIBILITY');
export const toggleKanbanFieldVisibility = createAction('TASKS_TOGGLE_KANBAN_FIELD_VISIBILITY');
export const toggleCalendarFieldVisibility = createAction('TASKS_TOGGLE_CALENDAR_FIELD_VISIBILITY');

export const loadList = createAction(
  'TASKS_LIST_LOAD_TASK_LIST',
  () => (dispatch, getState) => new Promise(resolve => {
    const state = getState();
    const navParams = listParamsNavSelector(state).toJS();
    const filtersParams = listParamsFiltersSelector(state).toJS();
    const params = {
      ...navParams,
      ...filtersParams,

      sort: currentSortSelector(state),
      order: currentOrderSelector(state)
    };

    return DpApi
      .sendGet('DP_API/tasks?' + Tasks.compileParams(params))
      .success(response => resolve(response.data));
  })
);

export const applySort = createAction(
  'TASKS_LIST_APPLY_SORT',
  value => dispatch => {
    dispatch(updateRoutingState('list', 'sort', value));
    dispatch(loadList());
  }
);

export const applyOrder = createAction(
  'TASKS_LIST_APPLY_ORDER',
  value => dispatch => {
    dispatch(updateRoutingState('list', 'order', value));
    dispatch(loadList());
  }
);

export const applyFilters = createAction(
  'TASKS_LIST_APPLY_FILTERS',
    value => dispatch => {
      dispatch(setListParamsFilters(value));
      dispatch(loadList());
    }
);
