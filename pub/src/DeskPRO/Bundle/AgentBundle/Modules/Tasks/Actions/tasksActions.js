import { createAction } from 'Ampliflux';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const setListParams = createAction('TASKS_SET_LIST_PARAMS');
export const loadList = createAction(
  'TASKS_LOAD_TASK_LIST',
  params => () => {
    // Make sure we don't accidentally break the filter details
    const filter = params;
    const filterElements = {};

    if (filter.done && filter.done !== 'all') {
      filterElements.is_done = (filter.done === 'done');
    }

    if (filter.projects && filter.projects.length > 0) {
      filterElements.project = filter.projects;
    }

    if (filter.agents && filter.agents.length > 0) {
      filterElements.assigned = filter.agents;
    }

    if (filter.teams && filter.teams.length > 0) {
      filterElements.assigned_team = filter.teams;
    }

    if (filter.departments && filter.departments.length > 0) {
      filterElements.assigned_department = filter.departments;
    }

    if (filter.creator) {
      filterElements.creator = filter.creator;
    }

    if (filter.labels && filter.labels.length > 0) {
      filterElements.labels = filter.labels;
    }

    if (filter.lists && filter.lists.length > 0) {
      filterElements.lists = filter.lists;
    }

    if (typeof filter.has_attachments !== 'undefined' && filter.has_attachments !== 'all') {
      filterElements.attachments = (filter.has_attachments === 'has') ? 'not_null' : 'null';
    }

    if (filter.created_after) {
      filterElements.created_after = filter.created_after;
    }

    if (filter.created_before) {
      filterElements.created_before = filter.created_before;
    }

    if (filter.due_after) {
      filterElements.due_after = filter.due_after;
    }

    if (filter.due_before) {
      filterElements.due_before = filter.due_before;
    }

    if (filter.done_after) {
      filterElements.done_after = filter.done_after;
    }

    if (filter.done_before) {
      filterElements.done_before = filter.done_before;
    }

    if (filter.page) {
      filterElements.page = filter.page;
    }

    if (filter.order_by) {
      filterElements.order_by = filter.order_by;
    } else {
      filterElements.order_by = 'due';
    }

    if (filter.sort) {
      filterElements.sort = filter.sort;
    } else {
      filterElements.sort = 'asc';
    }

    const compiled = 'tasks?' + Tasks.compileParams(filterElements);

    Tasks.loadAddress(compiled);

    return params;
  }
);

export const applyListParams = createAction(
  'TASKS_APPLY_LIST_PARAMS',
    params => dispatch => {
      dispatch(setListParams(params));
      dispatch(loadList(params));
    }
);
