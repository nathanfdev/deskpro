import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses, getLinkedData, replaceIds } from 'DeskPRO/Component/Util/Api';
import { setCollection, addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { setAgentSettings } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/settingsActions';
import { setAgentInfo } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/infoActions';
import { setupActionAlerts } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/notificationActions';
import { setImMe, loadDrafts } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Actions/messagesActions';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { updateAgentStatus, setOnlineAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Actions/agentActions';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import DeskproAppStore from 'DeskPRO/Bundle/AgentBundle/Modules/DeskproApps/DeskproAppStore';
import { setVoiceTokens, setVoiceActivities, setVoiceSettings } from '../../Voice/Actions/clientActions';
import { increaseCount, decreaseCount, loadGrouping } from '../../Filters/Actions/filterActions';

export const loadAgentPhraseTranslations = createAction(
  'AGENT_LOAD_PHRASE_TRANSLATIONS',
  () => () => new Promise((resolve) => {
    const language = window.DESKPRO_PERSON_LANG_ID;
    const buildNum = window.DP_VERSION_NUMBER;

    const setPhrases = (data) => {
      agentPhrases.setPhrases(data);
      resolve();
    };

    const cacheKey = `dpAgent.phrases.${language}.${buildNum}`;
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
        agents:                  { endpoint: 'agents' },
        languages:               { endpoint: 'languages', query: 'count=100' },
        user_groups:             { endpoint: 'user_groups' },
        settings:                { endpoint: 'helpdesk/agent-client/settings' },
        info:                    { endpoint: 'helpdesk/agent-client/info' },
        me:                      { endpoint: 'me' },
        agent_teams:             { endpoint: 'agent_teams' },
        my_agent_teams:          { endpoint: 'agent_teams', query: 'my=true' },
        ticket_departments:      { endpoint: 'ticket_departments', query: 'include=department_agent_ids' },
        my_ticket_departments:   { endpoint: 'ticket_departments', query: 'my=true&include=department_agent_ids' },
        chat_departments:        { endpoint: 'chat_departments', query: 'include=department_agent_ids' },
        onboardings:             { endpoint: 'people/onboarding/pending' },
        alerts:                  { endpoint: 'notify/setup/action-alerts' },
        user_chat_custom_fields: { endpoint: 'user_chat_custom_fields' },
        person_custom_fields:    { endpoint: 'person_custom_fields' },
        ticket_custom_fields:    { endpoint: 'ticket_custom_fields' },
        discover:                { endpoint: 'helpdesk/discover' }
      };

      if (window.DP_HAS_VOICE) {
        batchComponents.voice_tokens     = { endpoint: 'voice_client/tokens' };
        batchComponents.voice_activities = { endpoint: 'voice_client/activities' };
        batchComponents.voice_settings   = { endpoint: 'voice_settings' };
        batchComponents.voice_numbers    = { endpoint: 'voice_numbers' };
      }

      if (window.DP_HAS_NEW_IM) {
        batchComponents.defaultBrand  = { endpoint: 'brands/default' };
      }

      if (window.DP_HAS_NEW_SNIPPETS) {
        batchComponents.snippets  = {
          endpoint: 'snippets',
          query:    'count=200&inline_sideloads=true&include=snippet_translation,blob'
        };
        batchComponents.snippet_labels  = { endpoint: 'snippets/labels' };
      }

      if (window.DP_HAS_FOLLOW_UP) {
        batchComponents.ticket_macros  = { endpoint: 'ticket_macros' };
      }

      if (window.DP_HAS_NEW_FILTERS) {
        batchComponents.ticket_filters     = { endpoint: 'ticket_filters2', query: 'mine=1' };
        batchComponents.ticket_filter_sets = { endpoint: 'ticket_filters2_sets', query: 'mine=1' };
        batchComponents.ticket_stars       = { endpoint: 'ticket_stars' };
        batchComponents.ticket_labels      = { endpoint: 'ticket_labels' };
      }

      const onBatchComponentsSuccess = ({ responses }) => {
        const data = flattenBatchResponses(responses);

        dispatch(setCollection('Department', 'all_chat', data.chat_departments));
        dispatch(setCollection('Department', 'all_tickets', data.ticket_departments));
        dispatch(setCollection('Department', 'my_tickets', data.my_ticket_departments));
        dispatch(setCollection('Person', 'agents', data.agents));
        dispatch(setCollection('AgentTeam', 'all', data.agent_teams));
        dispatch(setCollection('AgentTeam', 'my', data.my_agent_teams));
        dispatch(setCollection('Language', 'all', data.languages));
        dispatch(setCollection('UserGroup', 'all', data.user_groups));
        dispatch(setCollection('UserChatCustomFields', 'all', data.user_chat_custom_fields));
        dispatch(setCollection('PersonCustomFields', 'all', data.person_custom_fields));
        dispatch(setCollection('TicketCustomFields', 'all', data.ticket_custom_fields));
        dispatch(setupActionAlerts(data.alerts));
        dispatch(updateAgentStatus({ agent_ids: data.agents.filter(a => a.online_for_chat).map(a => a.id) }));
        dispatch(setOnlineAgents(data.agents.filter(a => a.online).map(a => a.id)));

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
              const depAgents = agentIds.map(id => agents.get(id)).filter(agent => !!agent);
              dispatch(setCollection('Person', `department_${dep.id}`, depAgents));
            }
          }
        });

        dispatch(setAgentSettings(data.settings));
        dispatch(setAgentInfo(data.info));
        dispatch(setCollection('Person', 'me', [data.me.person]));

        if (window.DP_HAS_NEW_IM) {
          dispatch(setImMe(data.me.person));
          dispatch(loadDrafts());
          dispatch(setCollection('Brand', 'default', [data.defaultBrand]));
        }
        if (window.DP_HAS_VOICE) {
          dispatch(setVoiceTokens(data.voice_tokens));
          dispatch(setVoiceActivities(data.voice_activities));
          dispatch(setVoiceSettings(data.voice_settings));
          dispatch(setCollection('VoiceNumber', 'all', data.voice_numbers));
        }

        if (window.DP_HAS_NEW_SNIPPETS) {
          dispatch(setCollection('Snippets', 'all', data.snippets));
          dispatch(setCollection('SnippetLabels', 'all', data.snippet_labels));
          let blobs = [];
          if (responses.snippets.linked.blob) {
            blobs = responses.snippets.linked.blob;
          }
          dispatch(setCollection('SnippetBlobs', 'all', replaceIds(blobs, 'blob_id')));
          const pagination = responses.snippets.meta.pagination;
          let currentPage = pagination.current_page;
          while (currentPage < pagination.total_pages) {
            currentPage += 1;
            const extraSnippets = {
              endpoint: 'snippets',
              query:    `count=${pagination.count}&page=${currentPage}&inline_sideloads=true&include=snippet_translation,blob`
            };
            api.sendGet(api.prepareParams({ snippets: extraSnippets }))
              .success((response) => {
                dispatch(addToCollection('Snippets', 'all', response.responses.snippets.data));
                if (response.responses.snippets.linked.blob) {
                  dispatch(addToCollection('SnippetBlobs', 'all', replaceIds(response.responses.snippets.linked.blob, 'blob_id')));
                }
              })
            ;
          }
        }

        if (window.DP_HAS_FOLLOW_UP) {
          dispatch(setCollection('TicketMacros', 'all', data.ticket_macros));
        }

        if (window.DP_HAS_NEW_FILTERS) {
          dispatch(setCollection('TicketFilters', 'all', data.ticket_filters));
          dispatch(setCollection('TicketFilterSets', 'all', data.ticket_filter_sets));
          dispatch(setCollection('TicketStars', 'all', data.ticket_stars));
          dispatch(setCollection('TicketLabels', 'all', replaceIds(data.ticket_labels, 'label')));
          let counts = {};
          let i = 0;
          const groupingActions = [];
          data.ticket_filters.forEach((filter) => {
            counts[`filter${filter.id}`] = { endpoint: `ticket_filters2/${filter.id}/count` };
            i += 1;
            if (i >= 5) {
              api.sendGet(api.prepareParams(counts))
                .success((countsResponses) => {
                  const countsData = flattenBatchResponses(countsResponses.responses);
                  dispatch(addToCollection('TicketFilterCounts', 'all', countsData));
                });
              counts = {};
              i = 0;
            }
            const groupBy = localStorage.getItem(`column_filter_subfilter_${filter.id}`);
            if (groupBy && groupBy !== '@none') {
              groupingActions.push(loadGrouping(filter.id, groupBy));
            }
          });
          if (i > 0) {
            api.sendGet(api.prepareParams(counts))
              .success((countsResponses) => {
                const countsData = flattenBatchResponses(countsResponses.responses);
                dispatch(addToCollection('TicketFilterCounts', 'all', countsData));
              });
          }
          groupingActions.forEach(action => dispatch(action));
          window.increase_count = (id) => {
            dispatch(increaseCount(id));
          };
          window.decrease_count = (id) => {
            dispatch(decreaseCount(id));
          };
        }
        const starCounts = {
          endpoint: 'ticket_stars/counts',
        };
        api.sendGet(api.prepareParams({ star_counts: starCounts }))
          .success((response) => {
            if (response.responses.star_counts.data.nested) {
              dispatch(setCollection('TicketStarsCounts', 'all', response.responses.star_counts.data.nested));
            }
          });

        // set legacy agent notify map
        window.notifyAgentMap = {};
        data.agents.forEach((agent) => {
          if (data.me.person.id === agent.id) {
            return;
          }

          window.notifyAgentMap[agent.id] = {
            name:        agent.name,
            picture_url: (
              agent.avatar.url_pattern
              || (agent.avatar.base_gravatar_url ? `${agent.avatar.base_gravatar_url}?d=mm&s=20` : '')
              || agent.avatar.default_url_pattern).replace(/\{\{IMG_SIZE}}/, '20'
            )
          };
        });

        return data;
      };

      const phrasesLoad = dispatch(loadAgentPhraseTranslations());
      const batchLoad = api.sendGet(api.prepareParams(batchComponents))
        .success(onBatchComponentsSuccess)
        .then((responses) => {
          const { discover } = responses.data.responses;
          // create appstore configuration
          /** @var {AppsConfigBuilder} **/
          const builder = DeskproAppStore.configureWithWindowParams(window);

          if (discover && discover.data) {
            builder.addHelpdeskDiscoverySettings(discover.data);
          }
          const appStoreConfig = builder.build();

          // bootstrap appstore
          return DeskproAppStore.bootstrap(dispatch, api, appStoreConfig)
             .then(() => new Promise(appResolve => setTimeout(appResolve, 100)))
          ;
        })
        .then(() => {
          dispatch(donePreloading());
        })
      ;

      Promise.all([phrasesLoad, batchLoad]).then(() => resolve());
    }
  )
);

export const closeIframes = () => {
  if (!window.DP_FRAME_OVERLAYS) {
    return;
  }

  for (const key of Object.keys(window.DP_FRAME_OVERLAYS)) {
    const iframe = window.DP_FRAME_OVERLAYS[key];
    if (iframe.opened) {
      iframe.close();
    }
  }
};
