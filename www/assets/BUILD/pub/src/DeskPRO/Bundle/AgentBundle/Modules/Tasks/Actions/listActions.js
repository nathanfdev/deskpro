import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import { listParamsNavSelector, listParamsFiltersSelector, currentOrderBySelector, currentOrderDirSelector }
  from '../Selectors/list';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { addToCollection, setCollection, collectionSelectorFactory, removeFromCollection, releaseCollection }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import invariant from 'invariant';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { myAgentTeamsSelector, myDepartmentsSelector }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/common';
import { loadCounts } from './navActions';

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

        dispatch(releaseCollection('Task', 'all'));
        dispatch(setCollection('Task', 'all', res.data));
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
    dispatch(addToCollection('Task', 'all', Immutable.List([task])));
  })
);

const isTaskMatch = function (task, state) {
  let me;
  for (const [k, v] of listParamsNavSelector(state)) {
    switch (k) {
      // project
      case 'project':
        if (v.toSet().has(task.get('project'))) {
          return true;
        }
        break;

      // me or other agent
      case 'assigned_agent':
        const set = v.toSet();
        me = meSelector(state);
        if (set.has('me') && task.get('agents').toSet().has(me.get('id'))) {
          return true;
        }
        for (const i of task.get('agents')) {
          if (set.has(i)) {
            return true;
          }
        }
        break;

      // only other agent
      case 'not_assigned_agent':
        me = meSelector(state);
        if (!v.toSet().has('me')) {
          return true;
        }
        break;

      case 'no_assignments':
        if (task.get('agents').size === 0 && task.get('teams').size === 0 && task.get('departments').size === 0) {
          return true;
        }
        break;

      // my teams
      case 'assigned_team':
        const teams = myAgentTeamsSelector(state);
        for (const i of task.get('teams')) {
          if (teams.has(i)) {
            return true;
          }
        }
        break;

      // my departments
      case 'assigned_department':
        const deps = myDepartmentsSelector(state);
        for (const i of task.get('departments')) {
          if (deps.has(i)) {
            return true;
          }
        }
        break;

      default:
        break;
    }
  }

  return false;
};

export const getTask = createAction(
  'TASKS_LIST_GET_TASK',
  (id) => (dispatch, getState) => api.sendGet(`DP_API/tasks/${id}`).success(response => {
    if (!response.data) return;
    const task = Immutable.fromJS(response.data);

    if (isTaskMatch(task, getState())) {
      dispatch(addToCollection('Task', 'all', Immutable.List([task])));
    } else {
      dispatch(removeFromCollection('Task', 'all', [task.get('id')]));
    }
  })
);

const updates = {};

export const editTask = createAction(
  'TASKS_LIST_EDIT_TASK',
  (id, data) => (dispatch, getState) => {
    invariant(!!data, 'Where are the data?');

    const tasks = collectionSelectorFactory('Task', 'all')(getState());
    const oldTask = tasks.get(id);

    const promise = api.sendPut(`DP_API/tasks/${id}`, data);
    updates[id] = promise;

    // todo show errors (alert?)
    promise.success(() => {
      if (updates[id] !== promise) return;
      delete updates[id];
      dispatch(loadCounts());
      dispatch(getTask(id));
    }).error(() => {
      if (updates[id] !== promise) return;
      delete updates[id];
      dispatch(addToCollection('Task', 'all', Immutable.List([oldTask])));
    });
  }
);
