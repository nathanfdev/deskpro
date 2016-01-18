import { createAction } from 'Ampliflux';
import { loadOnlineAgents } from './peopleActions';
import { loadOptions, openWidget } from './dpWindowActions';
import { loadChatPhraseTranslations, loadChatInfo, setChatId, unsetChatId } from '../../Chat/Actions/chatActions';
import { loadTicketDisplayFields } from '../../Ticket/Actions/ticketActions';
import { widgetSessionCodeSelector } from '../Selectors/bootstrap';
import { widgetHasChatSelector, liveDemoSelector } from '../Selectors/dpWindow';
import { onlineAgentsCountSelector } from '../RecordStores/Selectors/peopleSelectors';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import PortalPhrases from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import history from '../../../Services/history';
import $ from 'jquery';

export const ajaxOptions = {crossDomain: true, dataType: 'json'};
export const addSessionCode = (state, params = {}) => {
  return {...params, __sid: widgetSessionCodeSelector(state)};
};

// Api actions
export const getSession = createAction(
  'WIDGET_GET_SESSION',
  () => new Promise(resolve =>
    DpApi
      .sendPost('DP_API/auth/get_session', {session_code: localStorage.getItem('dpWidget.sessionCode')}, {...ajaxOptions})
      .success(response => {
        localStorage.setItem('dpWidget.sessionCode', response.session_code);
        resolve(response);
      })
  )
);

export const reloadSettings = createAction('WIDGET_RELOAD_SETTINGS', settings => $.extend(true, {}, settings));
export const loadSettings = createAction(
  'WIDGET_LOAD_SETTINGS',
  () => new Promise(resolve =>
    DpApi
      .sendGet('DP_API/widget/settings', {...ajaxOptions})
      .success(response => resolve(response)))
);

export const loadPortalPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => DpApi
    .sendGet('DP_API/lang/widget-phrases.json', {...ajaxOptions})
    .success(response => {
      PortalPhrases.setPhrases(response);
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
      dispatch(openWidget());

      if (chatInfo.agent) {
        history.replace('/chat/active');
      } else if (chatInfo.need_validate_email) {
        history.replace('/chat/validation/email');
      } else {
        history.replace('/chat/waiting');
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
      dispatch(loadSettings()),
      dispatch(loadOptions(window.DP_OPTIONS)),
      dispatch(loadPortalPhraseTranslations()),
      dispatch(loadChatPhraseTranslations()),
      dispatch(loadTicketDisplayFields())
    ])
    .then(response => {
      const promise = dispatch(chatResume());
      promise.then(() => resolve(response));
    });
  })
);
