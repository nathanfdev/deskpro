import { createAction } from 'Ampliflux/actions';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import * as Teams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';

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

export const loadRecentAgents = createAction(
    'IM_LIST_LOAD_RECENT_AGENTS',
    (trigger) => {
        IM.loadLatest().then(
                promise => {
                    let chats = promise.getData().data;
                    let agents = [];
                    chats.forEach(function(chat) {
                        
                    });
                    trigger(agents);
                }
        );


        ;
    }
);