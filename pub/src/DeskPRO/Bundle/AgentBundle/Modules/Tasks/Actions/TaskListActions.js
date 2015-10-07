import { createAction } from 'Ampliflux/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as AgentTeams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export const loadTasks = createAction(
  'TASKS_LOAD_TASKS',
  (trigger) => {
    Tasks.loadTasksRemainingCount().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadProjects = createAction(
  'TASKS_LOAD_PROJECTS',
  (trigger) => {
    Tasks.loadProjects({is_done: false}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadMyTasks = createAction(
  'TASKS_LOAD_MY_TASKS',
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned: 'me'}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadTeamTasks = createAction(
  'TASKS_LOAD_TEAM_TASKS',
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned_team: 'me'}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDepartmentTasks = createAction(
  'TASKS_LOAD_DEPARTMENT_TASKS',
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned_department: 'me'}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDelegatedTasks = createAction(
  'TASKS_LOAD_DELEGATED_TASKS',
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned: 'not_me', creator: 'me'}).then(
    (value) => trigger(value.getData())
  );
  }
);

export const loadUnassignedTasks = createAction(
  'TASKS_LOAD_UNASSIGNED_TASKS',
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned: null, assigned_team: null, assigned_department: null}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadAgents = createAction(
  'TASKS_LOAD_AGENTS',
  (trigger) => {
    Tasks.loadAgents().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadLabels = createAction(
  'TASKS_LOAD_LABELS',
  (trigger) => {
    Tasks.loadLabels().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadTeams = createAction(
  'TASKS_LOAD_TEAMS',
  (trigger) => {
    Tasks.loadTeams().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDepartments = createAction(
  'TASKS_LOAD_DEPARTMENTS',
  (trigger) => {
    Tasks.loadDepartments().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadLists = createAction(
  'TASKS_LOAD_LISTS',
  (trigger, data) => {
    Tasks.loadLists(data).then(
      (value) => trigger(value.getData())
    );
  }
);

export const failedProject = createAction('TASKS_POST_PROJECT_FAIL');
export const createProject = createAction(
  'TASKS_POST_PROJECT',
  (trigger, data) => {
    Tasks.createProject(data).then(
      (value) => {
        trigger(value.getData());
        trigger(null, loadProjects());
      },
      (value) => trigger(value.xhr.responseJSON, failedProject)
    );
  }
);

export const editProject = createAction(
  'TASKS_EDIT_PROJECT',
  (trigger, data) => {
    const projectId = data.projectId;
    delete data.projectId;
    Tasks.editProject(projectId, data).then(
      (value) => {
        trigger(value.getData(), createProject);
        trigger(null, loadProjects());
      },
      (value) => {
        trigger(value.xhr.responseJSON, failedProject);
      }
    );
  }
);

export const loadTaskList = createAction(
  'TASKS_LOAD_TASK_LIST',
  (trigger, data) => {
    Tasks.loadAddress(data).then(
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

          Tasks.loadLinkedTickets({ids: linkedTickets.join(',')}).then((ticket) => {
            output.tickets = ticket.getData().data;
            trigger(output);
          });
        });
      }
    );
  }
);

export const loadFilter = createAction(
  'TASKS_LOAD_FILTER',
  (trigger, data)=> {
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

    trigger(null, loadTaskList(compiled));
  }
);

export const setFilter = createAction(
  'TASKS_SET_FILTER',
  (trigger, data) => {
    trigger(null, loadFilter(data));
    trigger(data);
  }
);

export const failedTask = createAction('TASKS_POST_TASK_FAIL');
export const createTask = createAction(
  'TASKS_POST_TASK',
  (trigger, data, source = 'tasks') => {
    Tasks.createTask(data).then(
      (value) => {
        trigger(value.getData());
        trigger(null, loadTaskList(source));
      },
      (value) => trigger(value.xhr.responseJSON, failedTask)
    );
  }
);

export const editTask = createAction(
  'TASKS_EDIT_TASK',
  (trigger, data, source = 'nowhere') => {
    const taskId = data.taskId;
    delete data.taskId;
    Tasks.editTask(taskId, data).then(
      (value) => {
        trigger(value.getData(), createTask);
        trigger(null, loadTaskList(source));
      },
      (value) => {
        trigger(value.xhr.responseJSON, failedTask);
      }
    );
  }
);

export const massEditTasks = createAction(
  'TASKS_MASS_EDIT_TASKS',
  (trigger, data, source = 'nowhere') => {
    Tasks.massEditTasks(data).then(
      (value) => {
        trigger(value.getData(), createTask);
        trigger(null, loadTaskList(source));
      },
      (value) => {
        trigger(value.xhr.responseJSON, failedTask);
      }
    );
  }
);
