import { createAction } from 'Ampliflux';
import { loadBatch } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { loadOnlineAgents } from './peopleActions';
import { loadOptions, openWidget } from './dpWindowActions';
import { pollingChat, setChatId, unsetChatId, setLastAgentId } from '../../Chat/Actions/chatActions';
import {
  widgetSessionCodeSelector,
  requireChatLoginSelector,
  requireChatEmailValidationSelector,
  widgetHasChatSelector,
  widgetLanguageSelector,
  widgetSessionChatIdSelector
} from '../Selectors/bootstrap';

import { liveDemoSelector } from '../Selectors/dpWindow';
import { onlineAgentsCountSelector } from '../Selectors/peopleSelectors';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import $ from 'jquery';
import lscache from 'lscache';

export const ajaxOptions = { crossDomain: true, dataType: 'json' };
export const addSessionCode = (state, params = {}) => ({ ...params, dpsid: widgetSessionCodeSelector(state) });
export const setSettings = createAction('WIDGET_SET_SETTINGS', settings => $.extend(true, {}, settings));

// Api actions
export const getSession = createAction(
  'WIDGET_GET_SESSION',
  () => dispatch => new Promise(resolve =>
    widgetApi
      .sendPost('DP_API/auth/session', { dpsid: localStorage.getItem('dpWidget.sessionCode') }, { ...ajaxOptions })
      .success(response => {
        const data = response.data;
        localStorage.setItem('dpWidget.sessionCode', data.session_code);
        dispatch(setSettings(data.global_settings));

        if (data.person) {
          dispatch(loadBatch('Person', [data.person], 'all'));
        }

        resolve(data);
      })
  )
);

export const reloadSettings = createAction(
  'WIDGET_RELOAD_SETTINGS',
    settings => (dispatch, getState) => {
      const state = getState();
      const requireChatLogin = requireChatLoginSelector(state);
      const requireChatEmailValidation = requireChatEmailValidationSelector(state);

      dispatch(setSettings(settings));

      // Display require chat login changes
      if (settings.chat.require_login !== requireChatLogin) {
        dispatch(openWidget());
      }

      // Display require email validation changes
      if (settings.chat.email_validation !== requireChatEmailValidation) {
        dispatch(openWidget());
      }
    }
);

export const loadPortalPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => (dispatch, getState) => new Promise(resolve => {
    const state = getState();
    const language = widgetLanguageSelector(state);

    const setPhrases = (data) => {
      portalPhrases.setPhrases(data);
      resolve();
    };

    const cacheKey = `dpWidget.phrases.${language}`;
    const cachedData = lscache.get(cacheKey);

    if (cachedData) {
      setPhrases(cachedData);
    } else {
      widgetApi
        .sendGet(`DP_API/lang/widget-phrases.json?language=${language}`, { ...ajaxOptions })
        .success(response => {
          setPhrases(response);
          lscache.set(cacheKey, response, 60);
        });
    }
  })
);

export const chatResume = createAction(
  'WIDGET_CHAT_RESUME',
  () => (dispatch, getState) => {
    const state = getState();
    const storedChatId = widgetSessionChatIdSelector(state);
    const storedLastAgentId = Number(localStorage.getItem('dpWidget.chat.lastAgentId'));
    const widgetHasChat = widgetHasChatSelector(state);
    const agentsCounts = onlineAgentsCountSelector(state);
    const liveDemo = liveDemoSelector(state);

    if (liveDemo || !widgetHasChat || !storedChatId || !agentsCounts) {
      return Promise.resolve(true);
    }

    // force reset chat state before initial load
    dispatch(setChatId(storedChatId));
    dispatch(setLastAgentId(storedLastAgentId));

    const promise = dispatch(pollingChat(storedChatId));
    promise.then(response => {
      const chatInfo = response.data.chat_info && response.data.chat_info.data;

      // Reset stored chat id on reload page if chat was ended
      if (!chatInfo || chatInfo.date_ended) {
        dispatch(unsetChatId());
      } else {
        dispatch(openWidget());
      }
    });

    return promise;
  }
);

export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => dispatch => new Promise(resolve => {
    Promise.all([
      dispatch(loadOnlineAgents()),
      dispatch(getSession()),
      dispatch(loadOptions(window.DP_OPTIONS)),
      dispatch(loadPortalPhraseTranslations())
    ])
    .then(response => {
      // possibly reload translations with proper user's lang
      // do it again when user's session is loaded
      dispatch(loadPortalPhraseTranslations());

      // try to resume chat
      const onFinish = () => {
        resolve(response);
      };

      const onError = data => {
        // Remove from local storage broken chat id
        if (data && data.code === 400 && data.message === 'wrong_session_code') {
          dispatch(unsetChatId());
        }

        onFinish();
      };

      const promise = dispatch(chatResume());
      promise.then(onFinish, onError);
    });
  })
);
