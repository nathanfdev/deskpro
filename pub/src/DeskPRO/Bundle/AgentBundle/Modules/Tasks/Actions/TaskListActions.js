import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import * as Tasks from "DeskPRO/Bundle/AgentBundle/Services/Api/Tasks";
import * as People from "DeskPRO/Bundle/AgentBundle/Services/Api/People";
import * as Departments from "DeskPRO/Bundle/AgentBundle/Services/Api/Departments";
import * as AgentTeams from "DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams";

export const loadTasks = createAction(
  "TASKS_LOAD_TASKS",
  (trigger) => {
    Tasks.loadTasksRemainingCount().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadProjects = createAction(
  "TASKS_LOAD_PROJECTS",
  (trigger) => {
    Tasks.loadProjects({is_done: false}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadMyTasks = createAction(
  "TASKS_LOAD_MY_TASKS",
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned: 'me'}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadTeamTasks = createAction(
  "TASKS_LOAD_TEAM_TASKS",
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned_team: 'me'}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDepartmentTasks = createAction(
  "TASKS_LOAD_DEPARTMENT_TASKS",
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned_department: 'me'}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDelegatedTasks = createAction(
  "TASKS_LOAD_DELEGATED_TASKS",
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned: 'not_me', creator: 'me'}).then(
    (value) => trigger(value.getData())
  );
  }
);

export const loadUnassignedTasks = createAction(
  "TASKS_LOAD_UNASSIGNED_TASKS",
  (trigger) => {
    Tasks.loadTasksRemainingCount({assigned: null, assigned_team: null, assigned_department: null}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadAgents = createAction(
  "TASKS_LOAD_AGENTS",
  (trigger) => {
    Tasks.loadAgents({not_me: 1, is_done: false}).then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadLabels = createAction(
  "TASKS_LOAD_LABELS",
  (trigger) => {
    Tasks.loadLabels().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadTeams = createAction(
  "TASKS_LOAD_TEAMS",
  (trigger) => {
    Tasks.loadTeams().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDepartments = createAction(
  "TASKS_LOAD_DEPARTMENTS",
  (trigger) => {
    Tasks.loadDepartments().then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadLists = createAction(
  "TASKS_LOAD_LISTS",
  (trigger, data) => {
    Tasks.loadLists(data).then(
      (value) => trigger(value.getData())
    );
  }
);

export const failedProject = createAction("TASKS_POST_PROJECT_FAIL");
export const createProject = createAction(
  "TASKS_POST_PROJECT",
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
  "TASKS_EDIT_PROJECT",
  (trigger, data) => {
    let projectId = data.projectId;
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
  "TASKS_LOAD_TASK_LIST",
  (trigger, data) => {
    Tasks.loadAddress(data).then(
      (value) => {
        let result = value.getData();

        let projects = [];
        let linked_items = [];
        let people = [];
        let departments = [];
        let teams = [];

        result.data.forEach(function(task) {
          if (task.project !== null && projects.indexOf(task.project) === -1) {
            projects.push(task.project);
          }

          if (task.linked_items !== null && linked_items.indexOf(task.linked_items.id) === -1) {
            linked_items.push(task.linked_items.id);
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
          Tasks.loadLinks({ids: linked_items.join(',')}),
          People.loadPeople({ids: people.join(',')}),
          Tasks.loadDepartments({ids: departments.join(',')}),
          AgentTeams.loadAgentTeams({ids: teams.join(',')})
        ]).then((ps) => {
          result['projects'] = ps[0].getData().data;
          result['linked_items'] = ps[1].getData().data;
          result['people'] = ps[2].getData().data;
          result['departments'] = ps[3].getData().data;
          result['teams'] = ps[4].getData().data;

          result['source'] = data;

        }).then(() => {
          let linked_tickets = [];
          result['linked_items'].forEach((value) => {
            if (value.ticket) {
              linked_tickets.push(value.ticket);
            }
          });

          Tasks.loadLinkedTickets({ids: linked_tickets.join(',')}).then((data) => {
            result['tickets'] = data.getData().data;
            trigger(result);
          });
        });
      }
    );
  }
);

export const failedTask = createAction("TASKS_POST_TASK_FAIL");
export const createTask = createAction(
  "TASKS_POST_TASK",
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
  "TASKS_EDIT_TASK",
  (trigger, data, source = 'nowhere') => {
    let taskId = data.taskId;
    delete data.taskId;
    Tasks.editTask(taskId, data).then(
      (value) => {
        trigger(value.getData(), createTask);
        trigger(null, loadTaskList(source));
      },
      (value) => {
        trigger(value.xhr.responseJSON, failedTask);
      }
    )
  }
);