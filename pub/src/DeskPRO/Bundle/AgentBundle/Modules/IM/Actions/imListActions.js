import { createAction } from 'Ampliflux';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';
import * as Teams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import _ from 'lodash';

export const loadAgents = createAction(
    'IM_LIST_LOAD_AGENTS',
    () => {
      return Agents.loadAgents().then(promise => promise.getData().data);
    }
);

export const loadTeams = createAction(
    'IM_LIST_LOAD_TEAMS',
    () => {
      return Teams.loadAll().then(promise => promise.getData().data);
    }
);

export const loadDepartments = createAction(
    'IM_LIST_LOAD_DEPARTMENTS',
    () => {
      return Departments.loadDepartments().then(promise => promise.getData().data);
    }
);

export const loadRecentAgents = createAction(
    'IM_LIST_LOAD_RECENT_AGENTS',
    () => {
      let agents = [];
      let teams = [];
      let departments = [];
      return IM.loadLatest().then(
        promise => {
          const chats = promise.getData().data;
          chats.map(chat => {
            agents = _.union(agents, chat.agents);
            teams = _.union(teams, chat.teams);
            departments = _.union(departments, chat.departments);
          });
          return new Promise(resolve => resolve(agents));
        }
      ).then(agents => {
        return Agents.loadAgents({ids: agents.join(',')});
      }).then(promise => {
        return promise.getData().data;
      });
    }
);