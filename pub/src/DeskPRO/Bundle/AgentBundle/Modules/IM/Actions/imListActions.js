import { createAction } from 'Ampliflux/actions';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';
import * as Teams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';

export const loadAgents = createAction(
    'IM_LIST_LOAD_AGENTS',
    (trigger) => {
        return Agents.loadAgents().then(promise => trigger(promise.getData().data))
    }

);

export const loadTeams = createAction(
    'IM_LIST_LOAD_TEAMS',
    (trigger) => {
        return Teams.loadAll().then(promise => trigger(promise.getData().data))
    }
);

export const loadDepartments = createAction(
    'IM_LIST_LOAD_DEPARTMENTS',
    (trigger) => {
        return Departments.loadDepartments().then(promise => trigger(promise.getData().data))
    }
);

//TODO just a stub right now
export const loadRecentAgents = createAction(
    'IM_LIST_LOAD_RECENT_AGENTS',
    (trigger) => {
        return Agents.loadAgents().then(promise => trigger(promise.getData().data))
    }
);