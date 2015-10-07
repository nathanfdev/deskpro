import { createAction } from 'Ampliflux';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as AgentTeams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

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
      (value) => value.getData()
    );
  }
);

export const failedProject = createAction('TASKS_POST_PROJECT_FAIL');
export const createProject = createAction(
  'TASKS_POST_PROJECT',
  (data) => {
    return Tasks.createProject(data).then(
      (value) => {
        value.getData();
        loadProjects();
      },
      (value) => value.xhr.responseJSON
    );
  }
);

export const editProject = createAction(
  'TASKS_EDIT_PROJECT',
  (data) => {
    const projectId = data.projectId;
    delete data.projectId;
    return Tasks.editProject(projectId, data).then(
      (value) => {
        value.getData();
        loadProjects();
      },
      (value) => value.xhr.responseJSON
    );
  }
);

export const loadTaskList = createAction(
  'TASKS_LOAD_TASK_LIST',
  (data) => {
    return Tasks.loadAddress(data).then(
      (value) => {
        const result = value.getData();
        const output = result;

        const projects = [];
        const linkedItems = [];
        const people = [];
        const departments = [];
        const teams = [];

        result.data.forEach((task) => {
          if (task.project !== null && projects.indexOf(task.project) === -1) {
            projects.push(task.project);
          }

          if (task.linked_items !== null && linkedItems.indexOf(task.linked_items.id) === -1) {
            linkedItems.push(task.linked_items.id);
          }

          if (task.agents !== null && typeof task.agents.forEach === 'function') {
            task.agents.forEach((agent) => {
              if (people.indexOf(agent) === -1) {
                people.push(agent);
              }
            });
          }

          if (task.departments !== null && typeof task.departments.forEach === 'function') {
            task.departments.forEach((department) => {
              if (departments.indexOf(department) === -1) {
                departments.push(department);
              }
            });
          }

          if (task.teams !== null && typeof task.teams.forEach === 'function') {
            task.teams.forEach((team) => {
              if (teams.indexOf(team) === -1) {
                teams.push(team);
              }
            });
          }
        });

        // Load all the relevant data, and when it's done fire the trigger
        Promise.all([
          Tasks.loadProjects({ids: projects.join(',')}),
          Tasks.loadLinks({ids: linkedItems.join(',')}),
          People.loadPeople({ids: people.join(',')}),
          Tasks.loadDepartments({ids: departments.join(',')}),
          AgentTeams.loadAgentTeams({ids: teams.join(',')})
        ]).then((ps) => {
          output.projects = ps[0].getData().data;
          output.linked_items = ps[1].getData().data;
          output.people = ps[2].getData().data;
          output.departments = ps[3].getData().data;
          output.teams = ps[4].getData().data;

          output.source = data;
        }).then(() => {
          const linkedTickets = [];
          output.linked_items.forEach((item) => {
            if (item.ticket) {
              linkedTickets.push(item.ticket);
            }
          });

          return Tasks.loadLinkedTickets({ids: linkedTickets.join(',')}).then((ticket) => {
            output.tickets = ticket.getData().data;
            return output;
            // trigger(output);
          });
        });
      }
    );
  }
);

export const loadFilter = createAction(
  'TASKS_LOAD_FILTER',
  (data)=> {
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

    return loadTaskList(compiled);
  }
);

export const setFilter = createAction(
  'TASKS_SET_FILTER',
  (data) => {
    loadFilter(data);
    return data;
  }
);

export const failedTask = createAction('TASKS_POST_TASK_FAIL');
export const createTask = createAction(
  'TASKS_POST_TASK',
  (data, source = 'tasks') => {
    return Tasks.createTask(data).then(
      (value) => {
        const output = value.getData();
        loadTaskList(source);
        return output;
      },
      (value) => value.xhr.responseJSON
    );
  }
);

export const editTask = createAction(
  'TASKS_EDIT_TASK',
  (data, source = 'nowhere') => {
    const taskId = data.taskId;
    delete data.taskId;
    return Tasks.editTask(taskId, data).then(
      (value) => {
        const output = value.getData();
        loadTaskList(source);
        return output;
      },
      (value) => value.xhr.responseJSON
    );
  }
);

export const massEditTasks = createAction(
  'TASKS_MASS_EDIT_TASKS',
  (data, source = 'nowhere') => {
    return Tasks.massEditTasks(data).then(
      (value) => {
        const output = value.getData();
        loadTaskList(source);
        return output;
      },
      (value) => value.xhr.responseJSON
    );
  }
);
