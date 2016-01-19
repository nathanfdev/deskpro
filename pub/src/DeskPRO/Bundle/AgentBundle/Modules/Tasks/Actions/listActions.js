import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import Immutable from 'immutable';
import { listParamsNavSelector, listParamsFiltersSelector, currentSortSelector, currentOrderSelector, elementsSelector }
  from '../Selectors/list';
import { tasksSelector } from '../Selectors/recordStores';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { reOrderCollection } from 'DeskPRO/Component/Util/DisplayOrder';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { setTaskListsRequest } from '../RecordStores/Actions/taskListActions.js';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'tasks';

export const setListParamsNav = createAction('TASKS_LIST_SET_PARAMS_NAV');
export const setListParamsFilters = createAction(
  'TASKS_LIST_SET_PARAMS_FILTERS',
  (overwrite) => (dispatch, getState) => {
    const current = listParamsFiltersSelector(getState()).toJS();
    return { ...current, ...overwrite };
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
  () => (dispatch, getState) => {
    const state = getState();
    const navState = listParamsNavSelector(state);
    if (!navState) {
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
      .then(promise => {
        const res = promise.getData();
        const ids = res.data.map(item=>item.id);

        dispatch(setTaskListsRequest(recordStoresId, res.data));

        return { ids: ids, pagination: res.meta.pagination };
      }
    );
  }
);

export const applySort = createAction(
  'TASKS_LIST_APPLY_SORT',
  (value) => dispatch => {
    dispatch(updateRoutingState('list', 'sort', value));
    dispatch(loadList());
  }
);

export const applyOrder = createAction(
  'TASKS_LIST_APPLY_ORDER',
  (value) => dispatch => {
    dispatch(updateRoutingState('list', 'order', value));
    dispatch(loadList());
  }
);

export const applyFilters = createAction(
  'TASKS_LIST_APPLY_FILTERS',
  (value) => dispatch => {
    dispatch(setListParamsFilters(value));
    dispatch(loadList());
  }
);

export const addTask = createAction(
  'TASKS_LIST_ADD_TASK',
  (data) => new Promise(resolve => {
    return DpApi
      .sendPost(`DP_API/tasks`, data)
      .success(response => resolve(response.data));
  })
);

export const editTask = createAction(
  'TASKS_LIST_EDIT_TASK',
  (taskId, data) => (dispatch, getState) => new Promise(resolve => {
    const state = getState();
    const ids = elementsSelector(state);
    const tasks = tasksSelector(state);
    const task = tasks.get(taskId);
    const taskIndex = ids.indexOf(taskId);

    const changedProps = Object.keys(data).filter(taskProp => task.get(taskProp) !== data[taskProp]);
    let updatedTasks = tasks;

    // Re order tasks
    updatedTasks = reOrderCollection(tasks, taskId, data.display_order);
    if (changedProps.indexOf('display_order') !== -1) {
      changedProps.splice(changedProps.indexOf('display_order'), 1);
    }

    // Update task props
    let updatedTask = updatedTasks.get(taskIndex);
    changedProps.forEach(changedProp => {
      let newValue = data[changedProp];
      if (Array.isArray(newValue)) {
        newValue = Immutable.fromJS(newValue);
      }

      updatedTask = updatedTask.set(changedProp, newValue);
    });

    updatedTasks = updatedTasks.set(taskIndex, updatedTask);
    DpApi.sendPut(`DP_API/tasks/${taskId}`, data).then(() => resolve(updatedTasks));
  })
);
