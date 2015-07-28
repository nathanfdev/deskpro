import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const loadTasks = createAction(
  "TASKS_LOAD_TASKS",
  (trigger) => {
    DpApi.sendGet('DP_API/tasks?count_only=true').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadProjects = createAction(
  "TASKS_LOAD_PROJECTS",
  (trigger) => {
    DpApi.sendGet('DP_API/projects').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadMyTasks = createAction(
  "TASKS_LOAD_MY_TASKS",
  (trigger) => {
    DpApi.sendGet('DP_API/tasks?assigned=me&count_only=true').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadTeamTasks = createAction(
  "TASKS_LOAD_TEAM_TASKS",
  (trigger) => {
    DpApi.sendGet('DP_API/tasks?assigned_team=me&count_only=true').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDepartmentTasks = createAction(
  "TASKS_LOAD_DEPARTMENT_TASKS",
  (trigger) => {
    DpApi.sendGet('DP_API/tasks?assigned_department=me&count_only=true').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDelegatedTasks = createAction(
  "TASKS_LOAD_DELEGATED_TASKS",
  (trigger) => {
  DpApi.sendGet('DP_API/tasks?assigned=not_me&creator=me&count_only=true').then(
    (value) => trigger(value.getData())
  );
  }
);

export const loadUnassignedTasks = createAction(
  "TASKS_LOAD_UNASSIGNED_TASKS",
  (trigger) => {
    DpApi.sendGet('DP_API/tasks?assigned=null&assigned_team=null&assigned_department=null&count_only=true').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadAgents = createAction(
  "TASKS_LOAD_AGENTS",
  (trigger) => {
    DpApi.sendGet('DP_API/people?is_agent=1&not_me=1').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadLabels = createAction(
  "TASKS_LOAD_LABELS",
  (trigger) => {
    DpApi.sendGet('DP_API/task_labels').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadTeams = createAction(
  "TASKS_LOAD_TEAMS",
  (trigger) => {
    DpApi.sendGet('DP_API/teams').then(
      (value) => trigger(value.getData())
    );
  }
);

export const loadDepartments = createAction(
  "TASKS_LOAD_DEPARTMENTS",
  (trigger) => {
    DpApi.sendGet('DP_API/departments').then(
      (value) => trigger(value.getData())
    );
  }
);

export const failedProject = createAction("TASKS_POST_PROJECT_FAIL");
export const createProject = createAction(
  "TASKS_POST_PROJECT",
  (trigger, data) => {
    DpApi.sendPost('DP_API/projects', data).then(
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
    DpApi.sendPut('DP_API/projects/' + projectId, data).then(
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
  (trigger, data, page = 1) => {
    DpApi.sendGet('DP_API/' + data).then(
      (value) => {
        let result = value.getData();
        result['source'] = data;
        trigger(result);
      }
    );
  }
);

export const failedTask = createAction("TASKS_POST_TASK_FAIL");
export const createTask = createAction(
  "TASKS_POST_TASK",
  (trigger, data) => {
    DpApi.sendPost('DP_API/tasks', data).then(
      (value) => {
        trigger(value.getData());
        trigger(null, loadTaskList());
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
    DpApi.sendPut('DP_API/tasks/' + taskId, data).then(
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