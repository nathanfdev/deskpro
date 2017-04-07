import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses, getLinkedData } from 'DeskPRO/Component/Util/Api';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setupActionAlerts } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/notificationActions';
import { setImMe, loadDrafts } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/messagesActions';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { setVoiceTokens, setVoiceActivities } from '../../Voice/Actions/clientActions';

export const loadAgentPhraseTranslations = createAction(
  'AGENT_LOAD_PHRASE_TRANSLATIONS',
  () => () => new Promise((resolve) => {
    const language = window.DESKPRO_PERSON_LANG_ID;

    const setPhrases = (data) => {
      agentPhrases.setPhrases(data);
      resolve();
    };

    const cacheKey = `dpAgent.phrases.${language}`;
    const cachedData = lscache.get(cacheKey);
    if (cachedData) {
      setPhrases(cachedData);
    } else {
      api
        .sendGet(`DP_API/languages/agent_phrases?language=${language}`)
        .success((response) => {
          setPhrases(response);
          lscache.set(cacheKey, response, 60);
        });
    }
  })
);
export const donePreloading = createAction('APP_BOOTSTRAP_DONE_PRELOADING');
export const preloadData    = createAction(
  'BOOTSTRAP_PRELOAD_DATA',
  () => (dispatch, getState) => new Promise(
    (resolve) => {
      const batchComponents = {
        agents:                { endpoint: 'agents' },
        languages:             { endpoint: 'languages' },
        user_groups:           { endpoint: 'user_groups' },
        settings:              { endpoint: 'helpdesk/agent-client/settings' },
        me:                    { endpoint: 'me' },
        agent_teams:           { endpoint: 'agent_teams' },
        my_agent_teams:        { endpoint: 'agent_teams', query: 'my=true' },
        ticket_departments:    { endpoint: 'ticket_departments', query: 'include=department_agent_ids' },
        my_ticket_departments: { endpoint: 'ticket_departments', query: 'my=true&include=department_agent_ids' },
        chat_departments:      { endpoint: 'chat_departments', query: 'include=department_agent_ids' },
        onboardings:           { endpoint: 'people/onboarding/pending' }
      };

      if (window.DP_HAS_VOICE) {
        batchComponents.voice_tokens     = { endpoint: 'voice_client/tokens' };
        batchComponents.voice_activities = { endpoint: 'voice_client/activities' };
        batchComponents.voice_numbers    = { endpoint: 'voice_numbers' };
      }

      if (window.DP_HAS_NEW_IM) {
        batchComponents.alerts = { endpoint: 'notify/setup/action-alerts' };
        batchComponents.defaultBrand  = { endpoint: 'brands/default' };
      }

      dispatch(loadAgentPhraseTranslations());

      api.sendGet(api.prepareParams(batchComponents))
        .success(({ responses }) => {
          const data = flattenBatchResponses(responses);

          dispatch(setCollection('Department', 'all_chat', data.chat_departments));
          dispatch(setCollection('Department', 'all_tickets', data.ticket_departments));
          dispatch(setCollection('Department', 'my_tickets', data.my_ticket_departments));
          dispatch(setCollection('Person', 'agents', data.agents));
          dispatch(setCollection('AgentTeam', 'all', data.agent_teams));
          dispatch(setCollection('AgentTeam', 'my', data.my_agent_teams));
          dispatch(setCollection('Language', 'all', data.languages));
          dispatch(setCollection('UserGroup', 'all', data.user_groups));

          if (data.onboardings) {
            dispatch(setCollection('Onboarding', 'pending', [data.onboardings]));
          }

          // group agents by departments
          const agents = agentsSelector(getState());

          ['chat_departments', 'ticket_departments'].forEach((depType) => {
            const linked = getLinkedData(responses, depType, 'department_agent_ids');
            for (const dep of data[depType]) {
              const agentIds = linked[dep.id];
              if (agentIds) {
                const depAgents = agentIds.map(id => agents.get(id));
                dispatch(setCollection('Person', `department_${dep.id}`, depAgents));
              }
            }
          });

          dispatch(setAgentSettings(data.settings));
          dispatch(setCollection('Person', 'me', [data.me.person]));

          if (window.DP_HAS_NEW_IM) {
            dispatch(setImMe(data.me.person));
            dispatch(setupActionAlerts(data.alerts));
            dispatch(loadDrafts());
            dispatch(setCollection('Brand', 'default', [data.defaultBrand]));
            // a simple way to subscribe TabBars events
          }
          if (window.DP_HAS_VOICE) {
            dispatch(setVoiceTokens(data.voice_tokens));
            dispatch(setVoiceActivities(data.voice_activities));
            dispatch(setCollection('VoiceNumber', 'all', data.voice_numbers));
          }

          dispatch(donePreloading());
        })
      ;

      return resolve();
    }
  )
);

