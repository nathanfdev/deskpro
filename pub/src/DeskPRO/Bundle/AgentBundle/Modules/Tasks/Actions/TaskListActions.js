import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const setLoadedTasks = createAction(ActionTypes.LOAD_TASKS);
export const setLoadedProjects = createAction(ActionTypes.LOAD_PROJECTS);
export const setLoadedMyTasks = createAction(ActionTypes.LOAD_MY_TASKS);
export const setLoadedTeamTasks = createAction(ActionTypes.LOAD_TEAM_TASKS);
export const setLoadedDelegatedTasks = createAction(ActionTypes.LOAD_DELEGATED_TASKS);
export const setLoadedUnassignedTasks = createAction(ActionTypes.LOAD_UNASSIGNED_TASKS);

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

        promises.push(DpApi.sendGet('DP_API/tasks?assigned='));

        Promise.all(promises).then((values) => {
            dispatch(setLoadedMyTasks(values[0].getData()));
        });
    }
};