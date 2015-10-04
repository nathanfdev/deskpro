import { createAction } from 'Ampliflux';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';
import * as Teams from 'DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import _ from 'lodash';

export const loadRecentAgents = createAction(
    'IM_LIST_LOAD_RECENT_AGENTS',
    () => {
      let agents = [];
      let teams = [];
      let departments = [];
      let recent = [];
      return IM.loadLatest().then(
        promise => {
          const chats = promise.getData().data;
          chats.map(chat => {
            agents = _.union(agents, chat.agents);
            teams = _.union(teams, chat.agent_teams);
            departments = _.union(departments, chat.departments);
          });
          return new Promise(resolve => resolve(agents));
        }
      ).then(agents => {
        return Agents.loadAgents({ids: agents.join(',')});
      }).then(promise => {
        promise.getData().data.map(datum => recent.push(datum));
        return teams
        // reduce teams, and everytime return promise, so we can loop it and at last return promise with proper data
        .reduce( (previous, team) => {
          return Teams.loadAgentTeamAgents(team).then(
            promise => {
              promise.getData().data.map(datum => recent.push(datum));
              return new Promise(resolve => resolve(team));
            }
          );
        }, true)
        .then(
          () => {
            return new Promise(resolve => resolve(recent));
          }
        );
      }).then(
          (afterTeams) => {
            return _.uniq(afterTeams, (agent) => {
              return agent.id;
            });
          }
      );
    }
);
