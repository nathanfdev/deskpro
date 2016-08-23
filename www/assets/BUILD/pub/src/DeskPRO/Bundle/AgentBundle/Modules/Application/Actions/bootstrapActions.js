import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setupActionAlerts } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/notificationActions';
import { setImMe } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/messagesActions';

export const donePreloading = createAction('APP_BOOTSTRAP_DONE_PRELOADING');
export const preloadData    = createAction(
  'BOOTSTRAP_PRELOAD_DATA',
  () => dispatch => new Promise(
    (resolve) => {
      const batchComponents = {
        chat_departments:      { endpoint: 'chat_departments' },
        agents:                { endpoint: 'agents' },
        languages:             { endpoint: 'languages' },
        user_groups:           { endpoint: 'user_groups' },
        settings:              { endpoint: 'helpdesk/agent-client/settings' },
        alerts:                { endpoint: 'notify/setup/action-alerts' },
        me:                    { endpoint: 'me' },
        agent_teams:           { endpoint: 'agent_teams' },
        my_agent_teams:        { endpoint: 'agent_teams', query: 'my=true' },
        ticket_departments:    { endpoint: 'ticket_departments' },
        my_ticket_departments: { endpoint: 'ticket_departments', query: 'my=true' },
        onboardings:           { endpoint: 'people/onboarding/new' }
      };
      const batch           = api.prepareParams(batchComponents);

      api.sendGet(batch)
        .success(({ responses }) => {
          const data = flattenBatchResponses(responses);
          dispatch(setCollection('Department', 'all_tickets', data.ticket_departments));
          dispatch(setCollection('Department', 'all_chat', data.chat_departments));
          dispatch(setCollection('Department', 'my_tickets', data.my_ticket_departments));
          dispatch(setCollection('Person', 'agents', data.agents));
          dispatch(setCollection('AgentTeam', 'all', data.agent_teams));
          dispatch(setCollection('AgentTeam', 'my', data.my_agent_teams));
          dispatch(setCollection('Language', 'all', data.languages));
          dispatch(setCollection('UserGroup', 'all', data.user_groups));
          if (data.onboardings) {
            dispatch(setCollection('Onboarding', 'new', [data.onboardings]));
          }
          dispatch(setAgentSettings(data.settings));
          dispatch(setupActionAlerts(data.alerts));
          dispatch(setCollection('Person', 'me', [data.me.person]));
          dispatch(setImMe(data.me.person));

          dispatch(donePreloading());
        })
      ;

      return resolve();
    }
  )
);
