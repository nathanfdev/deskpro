import { createAction } from 'Ampliflux';
import { loadBatch } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { loadOnlineAgents } from './peopleActions';
import { loadOptions, openWidget } from './dpWindowActions';
import { loadChatInfo, setChatId, unsetChatId, updateChatInfo } from '../../Chat/Actions/chatActions';
import {
  widgetSessionCodeSelector,
  requireChatLoginSelector,
  requireChatEmailValidationSelector,
  widgetHasChatSelector
} from '../Selectors/bootstrap';

import { liveDemoSelector } from '../Selectors/dpWindow';
import { widgetLanguageSelector } from '../Selectors/bootstrap';
import { onlineAgentsCountSelector } from '../Selectors/peopleSelectors';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import * as windowApiActions from '../../../Services/WindowApi';
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
    const storedChatId = Number(localStorage.getItem('dpWidget.chat.chatId'));
    const widgetHasChat = widgetHasChatSelector(state);
    const agentsCounts = onlineAgentsCountSelector(state);
    const liveDemo = liveDemoSelector(state);

    if (liveDemo || !widgetHasChat || !storedChatId || !agentsCounts) {
      return Promise.resolve(true);
    }

    const promise = dispatch(loadChatInfo(storedChatId));
    promise.then(chatInfo => {
      // Reset stored chat id on reload page if chat was ended
      if (chatInfo.date_ended) {
        dispatch(unsetChatId());
        return;
      }

      dispatch(setChatId(storedChatId));
      dispatch(updateChatInfo(chatInfo));
      dispatch(openWidget());
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
        windowApiActions.widgetLoaded();
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
