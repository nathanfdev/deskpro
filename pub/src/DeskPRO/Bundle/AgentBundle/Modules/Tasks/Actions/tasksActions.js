import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const setListParams = createAction('TASKS_SET_LIST_PARAMS');
export const loadList = createAction(
  'TASKS_LOAD_TASK_LIST',
  params => new Promise(resolve => {
    const filterElements = {};

    if (params.done && params.done !== 'all') {
      filterElements.is_done = (params.done === 'done');
    }
    if (params.projects && params.projects.length > 0) {
      filterElements.project = params.projects;
    }
    if (params.agents && params.agents.length > 0) {
      filterElements.assigned = params.agents;
    }
    if (params.teams && params.teams.length > 0) {
      filterElements.assigned_team = params.teams;
    }
    if (params.departments && params.departments.length > 0) {
      filterElements.assigned_department = params.departments;
    }
    if (params.creator) {
      filterElements.creator = params.creator;
    }
    if (params.labels && params.labels.length > 0) {
      filterElements.labels = params.labels;
    }
    if (params.page) {
      filterElements.page = params.page;
    }
    if (params.order_by) {
      filterElements.order_by = params.order_by;
    } else {
      filterElements.order_by = 'due';
    }
    if (params.sort) {
      filterElements.sort = params.sort;
    } else {
      filterElements.sort = 'asc';
    }

    return DpApi
      .sendGet('DP_API/tasks?' + Tasks.compileParams(filterElements))
      .success(response => resolve(response.data));
  })
);

export const applyListParams = createAction(
  'TASKS_APPLY_LIST_PARAMS',
    params => dispatch => {
      dispatch(setListParams(params));
      dispatch(loadList(params));
    }
);
