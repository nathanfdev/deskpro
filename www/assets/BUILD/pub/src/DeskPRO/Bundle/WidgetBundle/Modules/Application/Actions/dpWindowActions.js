import $ from 'jquery';
import lscache from 'lscache';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import {
  requireChatLoginSelector,
  widgetSessionIsLoginSelector,
  widgetHasChatSelector
} from '../Selectors/bootstrap';
import { onlineAgentsCountSelector } from '../Selectors/peopleSelectors';
import {
  chatBeginModeSelector,
  widgetTypeSelector,
  widgetProactiveChatSelector,
  helpButtonSelector,
  helpPopupSelector,
  liveDemoSelector,
  ticketDefaultDepartmentSelector,
  ticketSelectDepartmentTypeSelector
} from '../Selectors/dpWindow';
import { chatIdSelector, needValidateEmailSelector } from '../../Chat/Selectors/chat';
import { loadNewTicketForm } from '../../Ticket/Actions/ticketActions';
import { history, getLocation } from '../../../Services/history';
import { dispatchWidgetStatus } from '../../../Services/WindowApi';

const openChatBeginStageByMode = (chatBeginMode) => {
  switch (chatBeginMode) {
    case 'conversation':
      history.replace('/chat/begin/conversation');
      break;
    case 'form':
      history.replace('/chat/begin/form');
      break;
    case 'simple':
    default:
      history.replace('/chat/begin/simple');
      break;
  }
};
const openChatBeginStage = createAction(
  'WIDGET_OPEN_CHAT_BEGIN_STAGE',
  () => (dispatch, getState) => {
    const state = getState();

    const chatBeginMode = chatBeginModeSelector(state);
    const isLogin = widgetSessionIsLoginSelector(state);

    if (isLogin) {
      history.replace('/chat/begin/simple');
    } else {
      openChatBeginStageByMode(chatBeginMode);
    }
  }
);

export const widgetResize = createAction(
  'WIDGET_RESIZE',
  () => {
    const widgetFrameWindow = parent.window.widget_iframe;
    const $window = $(widgetFrameWindow);

    return {
      width:  $window.width(),
      height: Math.max(widgetFrameWindow.innerHeight, $window.height())
    };
  }
);

export const windowResize = createAction(
  'WINDOW_RESIZE',
  () => (dispatch) => {
    dispatch(widgetResize());

    const parentWindow = parent.window;
    const $window = $(parentWindow);

    return {
      width:  $window.width(),
      height: parentWindow.innerHeight > $window.height() ? parentWindow.innerHeight : $window.height()
    };
  }
);

export const closeWidget = createAction(
  'WIDGET_CLOSE',
  () => {
    setTimeout(() => dispatchWidgetStatus(), 1);
  }
);
export const openWidget = createAction(
  'WIDGET_OPEN',
  () => (dispatch, getState) => {
    const state = getState();
    const widgetHasChat = widgetHasChatSelector(state);
    const requireChatLogin = requireChatLoginSelector(state);
    const agentsCounts = onlineAgentsCountSelector(state);
    const liveDemo = liveDemoSelector(state);
    const chatId = chatIdSelector(state);
    const needValidateEmail = needValidateEmailSelector(state);

    if (widgetHasChat && (liveDemo || agentsCounts > 0)) {
      if (chatId && !liveDemo) {
        if (needValidateEmail) {
          history.replace('/chat/validation/email');
        } else {
          history.replace('/chat/active');
        }
      } else if (requireChatLogin) {
        history.replace('/chat/validation/login');
      } else {
        dispatch(openChatBeginStage());
      }
    } else {
      history.replace('/ticket/form');
    }

    dispatch(windowResize());
    setTimeout(() => dispatchWidgetStatus(), 1);

    return null;
  }
);

export const reopenWidget = createAction('WIDGET_REOPEN', () => (dispatch) => {
  getLocation((location) => {
    // if history location was changed then just reopen widget, no need to resolve path
    // /chat is default path
    if (location.pathname !== '/chat') {
      dispatch(windowResize());
    } else {
      dispatch(openWidget());
    }
  });

  return null;
});

export const loadOptions = createAction('WIDGET_OPTIONS', options => $.extend(true, {}, options));
export const fetchOptions = createAction(
  'WIDGET_FETCH_OPTIONS',
  () => dispatch => new Promise((resolve) => {
    const setOptions = (options) => {
      // we need to merge the fetched options with the 'window.DP_OPTIONS' to have per-page customization
      // (e.g. have Page A on your site can use different phrases than Page B)
      const mergedOptions = $.extend(true, {}, options, window.DP_OPTIONS);

      dispatch(loadOptions(mergedOptions));
      resolve();
    };

    // try to get data from local storage
    const cachedOptions = lscache.get('dpWidget.options');
    if (cachedOptions) {
      setOptions(cachedOptions);
    }

    const promise = widgetApi.sendGet('DP_API/widget/brand_options');
    promise.then((response) => {
      const options = response.data.data;

      setOptions(options);
      lscache.set('dpWidget.options', options, 15);
    });
  })
);
// live demo action
export const reloadOptions = createAction(
  'WIDGET_RELOAD_OPTIONS',
  options => (dispatch, getState) => {
    if (!options) {
      return;
    }

    // get old state options
    let state = getState();
    const buttonOptions = helpButtonSelector(state);
    const popupOptions = helpPopupSelector(state);

    const widgetType = widgetTypeSelector(state);
    const chatBeginMode = chatBeginModeSelector(state);
    const proactiveMode = widgetProactiveChatSelector(state);
    const defaultTicketDepartment = ticketDefaultDepartmentSelector(state);
    const ticketDepartmentType = ticketSelectDepartmentTypeSelector(state);

    // reload options and reselect options with new state values
    dispatch(loadOptions(options));
    state = getState();

    const newButtonOptions = helpButtonSelector(state);
    const newPopupOptions = helpPopupSelector(state);
    const newChatBeginMode = chatBeginModeSelector(state);

    // compare old/new options to decide if we need to open or close widget to display the changes
    const buttonOptionsHaveChanged = !newButtonOptions.equals(buttonOptions);
    const popupOptionsHaveChanged = !newPopupOptions.equals(popupOptions);
    const proactiveModeChanged = proactiveMode !== widgetProactiveChatSelector(state);
    const defaultTicketDepartmentChanged = defaultTicketDepartment !== ticketDefaultDepartmentSelector(state);
    const ticketDepartmentTypeChanged = ticketDepartmentType !== ticketSelectDepartmentTypeSelector(state);
    const widgetTypeChanged = widgetType !== widgetTypeSelector(state);

    if (buttonOptionsHaveChanged || popupOptionsHaveChanged || proactiveModeChanged) {
      // if button or popup options were changed
      // then we should also close widget to display them
      dispatch(closeWidget());
    } else if (widgetTypeChanged) {
      // if widget body type was changed ("column" or "bubble")
      // then we should open the widget to display that
      dispatch(reopenWidget());
    } else if (chatBeginMode !== newChatBeginMode) {
      openChatBeginStageByMode(newChatBeginMode);
      dispatch(reopenWidget());
    } else if (defaultTicketDepartmentChanged || ticketDepartmentTypeChanged) {
      history.replace('/ticket/form');
      dispatch(reopenWidget());
      dispatch(loadNewTicketForm());
    }
  }
);

export const openTriggerPopup = createAction('WIDGET_OPEN_TRIGGER_POPUP');
export const closeTriggerPopup = createAction('WIDGET_CLOSE_TRIGGER_POPUP');

let loginWindowOpened;
export const openLoginWindow = createAction(
  'WIDGET_OPEN_LOGIN_WINDOW',
  () => {
    const width = 575;
    const height = 515;

    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);

    if (loginWindowOpened) {
      loginWindowOpened.close();
    }

    loginWindowOpened = window.open(
      `${window.DP_HELPDESK_URL}focus-win/login`,
      '',
      `width=${width},height=${height},left=${left},top=${top},` +
      'resizable=1,directories=0,titlebar=0,location=0,status=0,toolbar=0,menubar=0'
    );
  }
);
