import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import {
  listParamsNavSelector,
  listParamsFiltersSelector,
  currentSortSelector,
  currentOrderSelector,
  elementsSelector,
  elementsMapSelector
} from '../Selectors/list';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';

export const setListParamsNav = createAction('TASKS_LIST_SET_PARAMS_NAV');
export const setListParamsFilters = createAction(
  'TASKS_LIST_SET_PARAMS_FILTERS',
  overwrite => (dispatch, getState) => {
    const current = listParamsFiltersSelector(getState()).toJS();
    return {...current, ...overwrite};
  }
);

export const toggleTableFieldVisibility = createAction('TASKS_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TASKS_LIST_TOGGLE_CARD_FIELD_VISIBILITY');
export const toggleKanbanFieldVisibility = createAction('TASKS_LIST_TOGGLE_KANBAN_FIELD_VISIBILITY');
export const toggleCalendarFieldVisibility = createAction('TASKS_LIST_TOGGLE_CALENDAR_FIELD_VISIBILITY');

export const toggleSelected = createAction('TASKS_LIST_TOGGLE_SELECTED');
export const toggleAll = createAction('TASKS_LIST_TOGGLE_ALL_ACTION');

export const unload = createAction('TASKS_LIST_UNLOAD');
export const loadList = createAction(
  'TASKS_LIST_LOAD',
  () => (dispatch, getState) => new Promise(resolve => {
    const state = getState();
    const navState = listParamsNavSelector(state);
    if (!navState) {
      resolve({});
      return null;
    }

    const navParams = navState.toJS();
    const filtersParams = listParamsFiltersSelector(state).toJS();
    const params = {
      ...navParams,
      ...filtersParams,

      sort: currentSortSelector(state),
      order: currentOrderSelector(state)
    };

    return DpApi
      .sendGet('DP_API/tasks?' + compileParams(params))
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

export const editTask = createAction(
  'TASKS_LIST_EDIT_TASK',
  (taskId, updateData) => (dispatch, getState) => {
    const state = getState();
    const tasksMap = elementsMapSelector(state);

    let tasks = elementsSelector(state);
    let updatingTask = tasksMap.get(taskId);

    const changedProps = Object.keys(updateData).filter(taskProp => updatingTask.get(taskProp) !== updateData[taskProp]);
    if (changedProps.length) {
      // Update task props
      const updatingIndex = tasks.indexOf(updatingTask);

      changedProps.forEach(changedProp => updatingTask = updatingTask.set(changedProp, updateData[changedProp]));
      tasks = tasks.set(updatingIndex, updatingTask);

      // Update display order of related tasks
      if (updateData.display_order) {
        tasks
          .filter(task => task.get('display_order') >= updateData.display_order)
          .forEach(task => {
            const index = tasks.indexOf(task);
            const newOrder = task.get('display_order') + 1;

            tasks = tasks.set(index, task.set('display_order', newOrder));
          })
        ;
      }

      DpApi.sendPut('DP_API/tasks/' + taskId, updateData);
    }

    return tasks;
  }
);
