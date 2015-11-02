import { createAction } from 'Ampliflux';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import * as RecordStoreTaskActions from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/taskActions';
import * as RecordStoreTaskListActions from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/taskListActions';
import * as RecordStoreTicketActions from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Actions/ticketActions';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

export const loadTasks = createAction(
  'TASKS_LOAD_TASKS',
  () => {
    return Tasks.loadTasksRemainingCount().then(
      result => result.getData()
    );
  }
);

export const loadProjects = createAction(
  'TASKS_LOAD_PROJECTS',
  () => {
    return Tasks.loadProjects({is_done: false}).then(
      value => value.getData()
    );
  }
);

export const loadMyTasks = createAction(
  'TASKS_LOAD_MY_TASKS',
  () => {
    return Tasks.loadTasksRemainingCount({assigned: 'me'}).then(
      (value) => value.getData()
    );
  }
);

export const loadTeamTasks = createAction(
  'TASKS_LOAD_TEAM_TASKS',
  () => {
    return Tasks.loadTasksRemainingCount({assigned_team: 'me'}).then(
      (value) => value.getData()
    );
  }
);

export const loadDepartmentTasks = createAction(
  'TASKS_LOAD_DEPARTMENT_TASKS',
  () => {
    return Tasks.loadTasksRemainingCount({assigned_department: 'me'}).then(
      (value) => value.getData()
    );
  }
);

export const loadDelegatedTasks = createAction(
  'TASKS_LOAD_DELEGATED_TASKS',
  () => {
    return Tasks.loadTasksRemainingCount({assigned: 'not_me', creator: 'me'}).then(
      (value) => value.getData()
    );
  }
);

export const loadUnassignedTasks = createAction(
  'TASKS_LOAD_UNASSIGNED_TASKS',
  () => {
    return Tasks.loadTasksRemainingCount({assigned: null, assigned_team: null, assigned_department: null}).then(
      (value) => value.getData()
    );
  }
);

export const loadAgents = createAction(
  'TASKS_LOAD_AGENTS',
  () => {
    return Tasks.loadAgents().then(
      (value) => value.getData()
    );
  }
);

export const loadLabels = createAction(
  'TASKS_LOAD_LABELS',
  () => {
    return Tasks.loadLabels().then(
      (value) => value.getData()
    );
  }
);

export const loadTeams = createAction(
  'TASKS_LOAD_TEAMS',
  () => {
    return Tasks.loadTeams().then(
      (value) => value.getData()
    );
  }
);

export const loadDepartments = createAction(
  'TASKS_LOAD_DEPARTMENTS',
  () => {
    return Tasks.loadDepartments().then(
      (value) => value.getData()
    );
  }
);

export const loadLists = createAction(
  'TASKS_LOAD_LISTS',
  (data) => {
    return Tasks.loadLists(data).then(
      value => dispatch => {
        const requestId = 'taskListFrame';
        const result = value.getData();
        dispatch(RecordStoreTaskListActions.setTaskListRequest(requestId, mapKeyedFromArray(result.data, 'id'), false, 'append'));
        return result;
      }
    );
  }
);

export const failedProject = createAction('TASKS_POST_PROJECT_FAIL');
export const createProject = createAction(
  'TASKS_POST_PROJECT',
  (data) => {
    return Tasks.createProject(data).then(
      value => dispatch => {
        value.getData();
        dispatch(loadProjects());
      },
      value => value.xhr.responseJSON
    );
  }
);

export const editProject = createAction(
  'TASKS_EDIT_PROJECT',
  (data) => {
    const projectId = data.projectId;
    delete data.projectId;
    return Tasks.editProject(projectId, data).then(
      value => dispatch => {
        value.getData();
        dispatch(loadProjects());
      },
      value => value.xhr.responseJSON
    );
  }
);

const getFieldIds = (field, tasks) => {
  let fieldIds = [];

  tasks.map((task) => {
    fieldIds = [...fieldIds, ...task[field]];
  });

  // Awesome one-liner to reduce an array to unique values
  return [...new Set(fieldIds)];
};

export const setSource = createAction(
  'TASKS_SET_SOURCE',
  data => data
);

export const loadTaskList = createAction(
  'TASKS_LOAD_TASK_LIST',
  (params) => dispatch => {
    const requestId = 'loadTaskList';
    dispatch(RecordStoreTaskActions.releaseTaskRequest(requestId));
    dispatch(RecordStoreTicketActions.releaseTicketRequest(requestId));

    const promise1 = Tasks.loadAddress(params).then((value) => {
      const result = value.getData();

      dispatch(RecordStoreTaskActions.setTaskRequest(requestId, mapKeyedFromArray(result.data, 'id'), false, 'append'));
      const ticketIds = getFieldIds('linked_tickets', result.data);
      dispatch(setSource(params));
      return dispatch(RecordStoreTicketActions.loadTickets(requestId, ticketIds));
    });

    return Promise.all([promise1]);
  }
);

export const loadFilter = createAction(
  'TASKS_LOAD_FILTER',
  data => dispatch => {
    // Make sure we don't accidentally break the filter details
    const filter = data;
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
      filterElements.sort = constants.ORDER_ASC;
    }

    const compiled = 'tasks?' + Tasks.compileParams(filterElements);

    dispatch(loadTaskList(compiled));

    return compiled;
  }
);

export const setFilter = createAction(
  'TASKS_SET_FILTER',
  data => dispatch => {
    dispatch(loadFilter(data));
    return data;
  }
);

export const failedTask = createAction('TASKS_POST_TASK_FAIL');
export const createTask = createAction(
  'TASKS_POST_TASK',
  (data, source = 'tasks') => {
    return Tasks.createTask(data).then(
      value => dispatch => {
        const output = value.getData();
        dispatch(loadTaskList(source));
        return output;
      },
      value => value.xhr.responseJSON
    );
  }
);

export const editTask = createAction(
  'TASKS_EDIT_TASK',
  (data, source = 'tasks', reload = false) => {
    const taskId = data.taskId;
    delete data.taskId;
    return Tasks.editTask(taskId, data).then(
      value => dispatch => {
        const output = value.getData();

        if (reload) {
          dispatch(loadTaskList(source));
        }

        return output;
      },
      value => value.xhr.responseJSON
    );
  }
);

export const massEditTasks = createAction(
  'TASKS_MASS_EDIT_TASKS',
  (data, source = 'tasks') => {
    return Tasks.massEditTasks(data).then(
      value => dispatch => {
        const output = value.getData();
        dispatch(loadTaskList(source));
        return output;
      },
      value => value.xhr.responseJSON
    );
  }
);
