import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const setLoadedTasks = createAction(ActionTypes.LOAD_TASKS);
export const setLoadedProjects = createAction(ActionTypes.LOAD_PROJECTS);

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
