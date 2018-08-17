import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

export const loadAdminPhraseTranslations = createAction(
  'ADMIN_LOAD_PHRASE_TRANSLATIONS',
  () => () => new Promise((resolve) => {
    const language = window.DP_PERSON_LANG_ID;
    const buildNum = window.DP_BUILD_ID;

    const setPhrases = (data) => {
      agentPhrases.setPhrases(data);
      resolve();
    };

    const cacheKey = `dpAdmin.phrases.${language}.${buildNum}`;
    const cachedData = lscache.get(cacheKey);
    if (cachedData) {
      setPhrases(cachedData);
    } else {
      api
        .sendGet(`DP_API/languages/admin_phrases?format=icu&language=${language}`)
        .success((response) => {
          setPhrases(response);
          lscache.set(cacheKey, response, 60);
        });
    }
  })
);
