import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses, getLinkedData } from 'DeskPRO/Component/Util/Api';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setupActionAlerts } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/notificationActions';
import { setImMe } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/messagesActions';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { setVoiceTokens, setVoiceActivities, voiceBootstrap } from '../../Voice/Actions/clientActions';
import { isVoiceEnabled } from '../../Voice/Selectors/client';

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
        chat_departments:      { endpoint: 'chat_departments', query: 'include=agents' },
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
        onboardings:           { endpoint: 'people/onboarding/pending' },
        voice_tokens:          { endpoint: 'voice_client/tokens' },
        voice_activities:      { endpoint: 'voice_client/activities' }
      };

      dispatch(loadAgentPhraseTranslations());

      api.sendGet(api.prepareParams(batchComponents))
        .success(({ responses }) => {
          const data = flattenBatchResponses(responses);

          dispatch(setCollection('Department', 'all_tickets', data.ticket_departments));
          const linked = getLinkedData(responses, 'chat_departments', 'agents');
          for (const dep of data.chat_departments) {
            if (linked[dep.id]) {
              dep.agents = linked[dep.id];
            } else {
              dep.agents = [];
            }
          }
          dispatch(setCollection('Department', 'all_chat', data.chat_departments));
          dispatch(setCollection('Department', 'my_tickets', data.my_ticket_departments));
          dispatch(setCollection('Person', 'agents', data.agents));
          dispatch(setCollection('AgentTeam', 'all', data.agent_teams));
          dispatch(setCollection('AgentTeam', 'my', data.my_agent_teams));
          dispatch(setCollection('Language', 'all', data.languages));
          dispatch(setCollection('UserGroup', 'all', data.user_groups));

          if (data.onboardings) {
            dispatch(setCollection('Onboarding', 'pending', [data.onboardings]));
          }

          dispatch(setAgentSettings(data.settings));
          dispatch(setupActionAlerts(data.alerts));
          dispatch(setCollection('Person', 'me', [data.me.person]));
          dispatch(setImMe(data.me.person));

          dispatch(setVoiceTokens(data.voice_tokens));
          dispatch(setVoiceActivities(data.voice_activities));

          const state = getState();
          const voiceEnabled = isVoiceEnabled(state);

          if (voiceEnabled) {
            dispatch(voiceBootstrap());
          }

          dispatch(donePreloading());
        })
      ;

      return resolve();
    }
  )
);

