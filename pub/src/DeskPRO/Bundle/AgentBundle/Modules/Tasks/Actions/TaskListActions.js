import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const setLoadedTasks = createAction(ActionTypes.TASKS_LOAD_TASKS);
export const setLoadedProjects = createAction(ActionTypes.TASKS_LOAD_PROJECTS);
export const setLoadedMyTasks = createAction(ActionTypes.TASKS_LOAD_MY_TASKS);
export const setLoadedTeamTasks = createAction(ActionTypes.TASKS_LOAD_TEAM_TASKS);
export const setLoadedDepartmentTasks = createAction(ActionTypes.TASKS_LOAD_DEPARTMENT_TASKS);
export const setLoadedDelegatedTasks = createAction(ActionTypes.TASKS_LOAD_DELEGATED_TASKS);
export const setLoadedUnassignedTasks = createAction(ActionTypes.TASKS_LOAD_UNASSIGNED_TASKS);
export const setLoadedAgents = createAction(ActionTypes.TASKS_LOAD_AGENTS);
export const setLoadedLabels = createAction(ActionTypes.TASKS_LOAD_LABELS);
export const setLoadedTeams = createAction(ActionTypes.TASKS_LOAD_TEAMS);
export const setLoadedDepartments = createAction(ActionTypes.TASKS_LOAD_DEPARTMENTS);
export const setCreatedProject = createAction(ActionTypes.TASKS_POST_PROJECT);
export const failedProject = createAction(ActionTypes.TASKS_POST_PROJECT_FAIL);

// TODO

export const loadTasks = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/tasks').then((value) => {
            dispatch(setLoadedTasks(value.getData()));
        });
    }
};

export const loadProjects = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/projects').then((value) => {
            dispatch(setLoadedProjects(value.getData()));
        });
    }
};

export const loadMyTasks = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/tasks?assigned=me').then((value) => {
            dispatch(setLoadedMyTasks(value.getData()));
        });
    }
};

export const loadTeamTasks = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/tasks?assigned_team=me').then((value) => {
            dispatch(setLoadedTeamTasks(value.getData()));
        });
    }
};

export const loadDepartmentTasks = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/tasks?assigned_department=me').then((value) => {
            dispatch(setLoadedDepartmentTasks(value.getData()));
        });
    }
};

export const loadDelegatedTasks = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/tasks?assigned=not_me&creator=me').then((value) => {
            dispatch(setLoadedDelegatedTasks(value.getData()));
        });
    }
};

export const loadUnassignedTasks = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/tasks?assigned=null&assigned_team=null&assigned_department=null').then((value) => {
            dispatch(setLoadedUnassignedTasks(value.getData()));
        });
    }
};

export const loadAgents = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/people?is_agent=1&not_me=1').then((value) => {
            dispatch(setLoadedAgents(value.getData()));
        });
    }
};

export const loadLabels = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/task_labels').then((value) => {
            dispatch(setLoadedLabels(value.getData()));
        });
    }
};

export const loadTeams = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/teams').then((value) => {
            dispatch(setLoadedTeams(value.getData()));
        });
    }
};

export const loadDepartments = () => {
    return dispatch => {
        DpApi.sendGet('DP_API/departments').then((value) => {
            dispatch(setLoadedDepartments(value.getData()));
        });
    }
};

export const createProject = (data) => {
    return dispatch => {
        DpApi.sendPost('DP_API/projects', data).then((value) => {
            dispatch(setCreatedProject(value.getData()));
        },
        (value) => {
            dispatch(failedProject(value.errors));
        });
    }
}
