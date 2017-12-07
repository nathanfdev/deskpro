import $ from 'jquery';
import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { loadBatch } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { loadOnlineAgents } from './peopleActions';
import { loadOptions, fetchOptions, openWidget, reopenWidget, closeWidget } from './dpWindowActions';
import { pollingChat, setChatId, unsetChatId, setLastAgentId } from '../../Chat/Actions/chatActions';
import { widgetHasChatSelector, widgetSessionChatIdSelector, buildNumSelector } from '../Selectors/bootstrap';
import { liveDemoSelector, noFetchOptionsSelector, widgetLanguageSelector, widgetEnabledSelector, jwtTokenSelector } from '../Selectors/dpWindow';
import { onlineAgentsCountSelector } from '../Selectors/peopleSelectors';

export const ajaxOptions = { crossDomain: true, dataType: 'json' };
export const setSettings = createAction('WIDGET_SET_SETTINGS', settings => $.extend(true, {}, settings));

// Api actions
export const setLiveDemoSession = createAction(
  'WIDGET_SET_LIVE_DEMO_SESSION',
  () => ({
    is_chat_granted: true
  })
);
export const getSession = createAction(
  'WIDGET_GET_SESSION',
  () => (dispatch, getState) => new Promise((resolve) => {
    const state = getState();
    const jwtToken = jwtTokenSelector(state);

    return widgetApi
      .sendPost('DP_API/auth/session', {
        trackVisitor: window.DP_SEND_VISITOR_TRACK || {},
        jwt:          jwtToken
      }, { ...ajaxOptions })
      .success((response) => {
        const data = response.data;

        dispatch(setSettings(data.global_settings));
        resolve(data);

        if (data.person) {
          dispatch(loadBatch('Person', [data.person], 'all'));
        }
      });
  })
);

// live demo action
export const reloadSettings = createAction(
  'WIDGET_RELOAD_SETTINGS',
    settings => (dispatch, getState) => {
      let state = getState();

      // get current settings
      const widgetHasChat = widgetHasChatSelector(state);

      // update settings state and re-select settings
      dispatch(setSettings(settings));
      state = getState();

      const newWidgetHasChat = widgetHasChatSelector(state);

      // compare old/new settings to decide if we need to open or close widget to display the changes
      const widgetHasChatChanged = widgetHasChat !== widgetHasChatSelector(state);
      if (widgetHasChatChanged) {
        // if we turned on the "chat enabled" option
        // then reopen the widget or else we close it
        if (newWidgetHasChat) {
          dispatch(reopenWidget());
        } else {
          dispatch(closeWidget());
        }
      }
    }
);

export const loadPortalPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => (dispatch, getState) => new Promise((resolve) => {
    const state = getState();
    const language = widgetLanguageSelector(state);
    const buildNum = buildNumSelector(state);

    const setPhrases = (data) => {
      portalPhrases.setPhrases(data);
      resolve();
    };

    const cacheKey = `dpWidget.phrases.${language}.${buildNum}`;
    const cachedData = lscache.get(cacheKey);

    if (cachedData) {
      setPhrases(cachedData);
    } else {
      widgetApi
        .sendGet(`DP_API/lang/widget-phrases.json?language=${language}`, { ...ajaxOptions })
        .success((response) => {
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
    const storedLastAgentId = storageAvailable('localStorage') ? Number(localStorage.getItem('dpWidget.chat.lastAgentId')) : null;
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
    promise.then((response) => {
      const chatInfo = response.data.chat_info && response.data.chat_info.data;

      // Reset stored chat id on reload page if chat was ended
      if (!chatInfo || chatInfo.date_ended) {
        dispatch(unsetChatId());
      } else if (!storageAvailable('sessionStorage') || !sessionStorage['dpWidget.dpWindow.minimized']) {
        dispatch(openWidget());
      }
    });

    return promise;
  }
);

export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => (dispatch, getState) => new Promise((resolve) => {
    // load widget options first to choose which mode to use ("normal" or "demo")
    const options = window.DP_OPTIONS;

    dispatch(loadOptions(options));
    let state = getState();

    const liveDemo = liveDemoSelector(state);
    const noFetchOptions = noFetchOptionsSelector(state);

    if (liveDemo) {
      // bootstrap live demo mode
      Promise.all([
        dispatch(setLiveDemoSession()),
        dispatch(loadPortalPhraseTranslations())
      ])
      .then(() => resolve());
    } else {
      // bootstrap normal mode
      const promises = [
        dispatch(getSession()),
        dispatch(loadPortalPhraseTranslations())
      ];

      // if we load the widget from the portal then we can pass all options through 'window.DP_OPTIONS'
      // so we don't need to do extra api call to get them
      // otherwise we have short 'DP_OPTIONS' config with just helpdesk url and get others via the api request and cache it
      if (!noFetchOptions) {
        promises.push(dispatch(fetchOptions()));
      }

      Promise.all(promises).then((response) => {
        // get updated state after the ajax requests
        state = getState();

        const enabled = widgetEnabledSelector(state);
        if (enabled) {
          // possibly reload translations with proper user's lang
          // do it again when user's session is loaded
          dispatch(loadPortalPhraseTranslations());
          dispatch(loadOnlineAgents()).then(() => {
            // try to resume chat
            const onFinish = () => {
              resolve(response);
            };

            const onError = () => {
              dispatch(unsetChatId());
              onFinish();
            };

            const promise = dispatch(chatResume());
            promise.then(onFinish, onError);
          });
        }
      });
    }
  })
);
