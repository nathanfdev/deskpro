import { createAction } from 'Ampliflux';
import { loadOnlineAgents } from './peopleActions';
import { loadOptions, openWidget } from './dpWindowActions';
import { loadChatPhraseTranslations, loadChatInfo, setChatId, unsetChatId, updateChatInfo } from '../../Chat/Actions/chatActions';
import { loadTicketDisplayFields } from '../../Ticket/Actions/ticketActions';
import { widgetSessionCodeSelector, requireChatLoginSelector, requireChatEmailValidationSelector } from '../Selectors/bootstrap';
import { widgetHasChatSelector, liveDemoSelector } from '../Selectors/dpWindow';
import { onlineAgentsCountSelector } from '../Selectors/peopleSelectors';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import * as windowApiActions from '../../../Services/WindowApi';
import $ from 'jquery';

export const ajaxOptions = {crossDomain: true, dataType: 'json'};
export const addSessionCode = (state, params = {}) => {
  return {...params, __sid: widgetSessionCodeSelector(state)};
};

// Api actions
export const getSession = createAction(
  'WIDGET_GET_SESSION',
  () => new Promise(resolve =>
    widgetApi
      .sendPost('DP_API/auth/get_session', {session_code: localStorage.getItem('dpWidget.sessionCode')}, {...ajaxOptions})
      .success(response => {
        localStorage.setItem('dpWidget.sessionCode', response.session_code);
        resolve(response);
      })
  )
);

export const setSettings = createAction('WIDGET_SET_SETTINGS', settings => $.extend(true, {}, settings));
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
export const loadSettings = createAction(
  'WIDGET_LOAD_SETTINGS',
  () => dispatch =>
    widgetApi
      .sendGet('DP_API/widget/settings', {...ajaxOptions})
      .success(response => dispatch(setSettings(response.data)))
);

export const loadPortalPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => widgetApi
    .sendGet('DP_API/lang/widget-phrases.json', {...ajaxOptions})
    .success(response => {
      portalPhrases.setPhrases(response);
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
      dispatch(loadSettings()),
      dispatch(loadOptions(window.DP_OPTIONS)),
      dispatch(loadPortalPhraseTranslations()),
      dispatch(loadChatPhraseTranslations()),
      dispatch(loadTicketDisplayFields())
    ])
    .then(response => {
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
