import { createAction } from 'Ampliflux';
import {
  widgetOpenedSelector,
  widgetTypeSelector,
  chatBeginModeSelector,
  widgetHasChatSelector,
  helpPopupTitleSelector,
  helpPopupMessageSelector,
  helpPopupReplyTypeSelector
} from '../Selectors/dpWindow';
import $ from 'jquery';
import history from '../../../Services/history';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { addSessionCode } from './bootstrapActions';

export const openChatBeginStage = chatBeginMode => {
  switch (chatBeginMode) {
    case 'simple':
    default:
      history.replace('/chat/begin/simple');
      break;
    case 'conversation':
      history.replace('/chat/begin/conversation');
      break;
    case 'form':
      history.replace('/chat/begin/form');
      break;
  }
};

export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');

export const widgetResize = createAction(
  'WIDGET_RESIZE',
  () => {
    const $window = $(window.widgetFrame);
    return {
      width: $window.width(),
      height: $window.height()
    };
  }
);

export const windowResize = createAction(
  'WINDOW_RESIZE',
  () => dispatch => {
    dispatch(widgetResize());
    const $window = $(parent.window);

    return {
      width: $window.width(),
      height: $window.height()
    };
  }
);

export const loadOptions = createAction('WIDGET_OPTIONS', options => $.extend(true, {}, options));
export const reloadOptions = createAction(
  'WIDGET_RELOAD_OPTIONS',
  options => (dispatch, getState) => {
    const state = getState();

    const widgetOpened = widgetOpenedSelector(state);
    const widgetType = widgetTypeSelector(state);
    const chatEnabled = widgetHasChatSelector(state);
    const chatBeginMode = chatBeginModeSelector(state);

    dispatch(loadOptions(options));
    dispatch(windowResize());

    // Display widget type changes
    if (widgetOpened && widgetType !== options.widget.type) {
      dispatch(closeWidget());
      setTimeout(() => dispatch(openWidget()), 350);
    }

    const openChat = () => openChatBeginStage(options.chat.requestUserInfo ? options.chat.beginMode : 'simple');

    // Display chat toggle enabled changes
    if (chatEnabled !== options.chat.enabled) {
      if (options.chat.enabled) {
        openChat();
      } else {
        history.replace('/ticket/form');
      }

      if (!widgetOpened) {
        dispatch(openWidget());
      }
    }

    // Display chat begin stage changes
    if (options.chat.enabled && ((chatBeginMode !== 'simple' && !options.chat.requestUserInfo) || chatBeginMode !== options.chat.beginMode)) {
      openChat();
      if (!widgetOpened) {
        dispatch(openWidget());
      }
    }

    // Display chat popup changes
    const popupTitle = helpPopupTitleSelector(state);
    const popupMessage = helpPopupMessageSelector(state);
    const popupReplyType = helpPopupReplyTypeSelector(state);
    const newPopup = options.chat.popup;

    if (widgetOpened && (popupTitle !== newPopup.title || popupMessage !== newPopup.message || popupReplyType !== newPopup.replyType)) {
      dispatch(closeWidget());
    }
  }
);

export const openTriggerPopup = createAction('WIDGET_OPEN_TRIGGER_POPUP');
export const closeTriggerPopup = createAction('WIDGET_CLOSE_TRIGGER_POPUP');

let loginWindowOpened;
export const openLoginWindow = createAction(
  'WIDGET_OPEN_LOGIN_WINDOW',
  () => (dispatch, getState) => {
    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    const width = 575;
    const height = 515;

    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);

    if (loginWindowOpened) {
      loginWindowOpened.close();
    }

    loginWindowOpened = window.open(
      window.DP_HELPDESK_URL + `focus-win/login?${queryParams}`,
      '',
      `width=${width},height=${height},left=${left},top=${top},` +
      `resizable=1,directories=0,titlebar=0,location=0,status=0,toolbar=0,menubar=0`
    );
  }
);
