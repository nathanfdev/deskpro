import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const setLoadedTasks = createAction(ActionTypes.LOAD_TASKS);
export const setLoadedProjects = createAction(ActionTypes.LOAD_PROJECTS);
export const setLoadedMyTasks = createAction(ActionTypes.LOAD_MY_TASKS);
export const setLoadedTeamTasks = createAction(ActionTypes.LOAD_TEAM_TASKS);
export const setLoadedDepartmentTasks = createAction(ActionTypes.LOAD_DEPARTMENT_TASKS);
export const setLoadedDelegatedTasks = createAction(ActionTypes.LOAD_DELEGATED_TASKS);
export const setLoadedUnassignedTasks = createAction(ActionTypes.LOAD_UNASSIGNED_TASKS);
export const setLoadedAgents = createAction(ActionTypes.LOAD_AGENTS);
export const setLoadedLabels = createAction(ActionTypes.LOAD_LABELS);

// TODO

export const loadTasks = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/tasks'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedTasks(values[0].getData()));
        });
    }
};

export const loadProjects = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/projects'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedProjects(values[0].getData()));
        });
    }
};

export const loadMyTasks = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/tasks?assigned=me'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedMyTasks(values[0].getData()));
        });
    }
};

export const loadTeamTasks = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/tasks?assigned_team=me'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedTeamTasks(values[0].getData()));
        });
    }
};

export const loadDepartmentTasks = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/tasks?assigned_department=me'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedDepartmentTasks(values[0].getData()));
        });
    }
};

export const loadDelegatedTasks = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/tasks?assigned=not_me&creator=me'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedDelegatedTasks(values[0].getData()));
        });
    }
};

export const loadUnassignedTasks = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/tasks?assigned=null&assigned_team=null&assigned_department=null'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedUnassignedTasks(values[0].getData()));
        });
    }
};

export const loadAgents = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/people?is_agent=1&not_me=1'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedAgents(values[0].getData()));
        });
    }
};

export const loadLabels = () => {
    return dispatch => {
        let promises = [];

        promises.push(DpApi.sendGet('DP_API/task_labels'));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedLabels(values[0].getData()));
        });
    }
};
