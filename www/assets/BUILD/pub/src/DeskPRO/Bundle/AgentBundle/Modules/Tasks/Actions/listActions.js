import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import { listParamsNavSelector, listParamsFiltersSelector, currentOrderBySelector, currentOrderDirSelector }
  from '../Selectors/list';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { addToCollection, setCollection, releaseCollection, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'all';

const prepareLinkedData = (linked) => {
  const result = [];
  for (const key in linked) {
    if (linked.hasOwnProperty(key)) {
      result.push(linked[key]);
    }
  }
  return result;
};

export const setListParamsNav = createAction('TASKS_LIST_SET_PARAMS_NAV');
export const setListParamsFilters = createAction(
  'TASKS_LIST_SET_PARAMS_FILTERS',
  (overwrite) => (dispatch, getState) => {
    const current = listParamsFiltersSelector(getState()).toJS();
    if (overwrite.order_by) {
      dispatch(updateRoutingState('list', 'order_by', overwrite.order_by));
    }
    if (overwrite.order_dir) {
      dispatch(updateRoutingState('list', 'order_dir', overwrite.order_dir));
    }

    return { ...current, ...overwrite };
  }
);

export const toggleTableFieldVisibility = createAction('TASKS_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TASKS_LIST_TOGGLE_CARD_FIELD_VISIBILITY');
export const toggleKanbanFieldVisibility = createAction('TASKS_LIST_TOGGLE_KANBAN_FIELD_VISIBILITY');
export const toggleCalendarFieldVisibility = createAction('TASKS_LIST_TOGGLE_CALENDAR_FIELD_VISIBILITY');

export const toggleSelected = createAction('TASKS_LIST_TOGGLE_SELECTED');
export const toggleAll = createAction('TASKS_LIST_TOGGLE_ALL_ACTION');

export const loadIndicator = createAction('TASKS_LIST_LOAD_INDICATOR');
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
      order_by:  currentOrderBySelector(state),
      order_dir: currentOrderDirSelector(state),
      include:   'ticket,chat_conversation,article'
    };

    return api
      .sendGet(`DP_API/tasks?${compileParams(params)}`)
      .then(promise => {
        const res = promise.getData();
        const ids = res.data.map(item => item.id);

        dispatch(addToCollection('Ticket', 'all', prepareLinkedData(res.linked.ticket)));
        dispatch(addToCollection('Article', 'all', prepareLinkedData(res.linked.article)));
        dispatch(addToCollection('UserChat', 'all', prepareLinkedData(res.linked.chat_conversation)));

        dispatch(releaseCollection('Task', recordStoresId));
        dispatch(setCollection('Task', recordStoresId, res.data));
        const { pagination } = res.meta;
        return { ids, pagination };
      }
      );
  }
);

export const applyOrderBy = createAction(
  'TASKS_LIST_APPLY_ORDER_BY',
  (value) => dispatch => {
    dispatch(updateRoutingState('list', 'order_by', value));
    dispatch(loadList());
  }
);

export const applyOrderDir = createAction(
  'TASKS_LIST_APPLY_ORDER_DIR',
  (value) => dispatch => {
    dispatch(updateRoutingState('list', 'order_dir', value));
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
  (data) => dispatch => api.sendPost('DP_API/tasks', data).success(response => {
    const task = Immutable.fromJS(response.data);
    dispatch(addToCollection('Task', recordStoresId, Immutable.List([task])));
  })
);

let updateRequests = 0;
export const editTask = createAction(
  'TASKS_LIST_EDIT_TASK',
  (taskId, data) => (dispatch, getState) => {
    let tasks = collectionSelectorFactory('Task', recordStoresId)(getState());
    const oldTask = tasks.get(taskId);
    let newTask = tasks.get(taskId);

    newTask = newTask.withMutations(map => {
      for (const key of Object.keys(data)) {
        const value = Immutable.fromJS(data[key]);
        map.set(key, value);
      }
    });

    if (Immutable.is(newTask, oldTask)) {
      return;
    }

    const promise = api.sendPut(`DP_API/tasks/${taskId}`, data);
    updateRequests++;

    // todo show errors (alert?)
    promise.success(() => {
      if (--updateRequests > 1) return;
      tasks = collectionSelectorFactory('Task', recordStoresId)(getState()).set(taskId, newTask);
      dispatch(setCollection('Task', recordStoresId, tasks));
    }).error(() => {
      if (--updateRequests > 1) return;
      tasks = collectionSelectorFactory('Task', recordStoresId)(getState()).set(taskId, oldTask);
      dispatch(setCollection('Task', recordStoresId, tasks));
    });
  }
);
