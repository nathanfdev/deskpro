import { createAction } from 'Ampliflux';
import { requireChatLoginSelector } from '../Selectors/bootstrap';
import { onlineAgentsCountSelector } from '../RecordStores/Selectors/peopleSelectors';
import {
  chatBeginModeSelector,
  widgetHasChatSelector,
  widgetRawPositionSelector,
  helpButtonSelector,
  helpPopupSelector,
  liveDemoSelector,
  agentAcceptTimeoutSelector,
  agentPollingTimeoutSelector
} from '../Selectors/dpWindow';
import {
  chatIdSelector,
  agentIdSelector,
  dateEndedSelector,
  needValidateEmailSelector
} from '../../Chat/Selectors/chat';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { addSessionCode } from './bootstrapActions';
import $ from 'jquery';
import history from '../../../Services/history';
import Immutable from 'immutable';

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

export const closeWidget = createAction('WIDGET_CLOSE');
export const openWidget = createAction(
  'WIDGET_OPEN',
  () => (dispatch, getState) => {
    const state = getState();

    const widgetHasChat = widgetHasChatSelector(state);
    const requireChatLogin = requireChatLoginSelector(state);
    const agentsCounts = onlineAgentsCountSelector(state);
    const liveDemo = liveDemoSelector(state);
    const chatId = chatIdSelector(state);
    const chatBeginMode = chatBeginModeSelector(state);
    const agentId = agentIdSelector(state);
    const dateEnded = dateEndedSelector(state);
    const needValidateEmail = needValidateEmailSelector(state);

    if (widgetHasChat && (liveDemo || agentsCounts > 0)) {
      if (chatId && !liveDemo) {
        if (agentId || dateEnded) {
          history.replace('/chat/active');
        } else if (needValidateEmail) {
          history.replace('/chat/validation/email');
        } else {
          history.replace('/chat/waiting');
        }
      } else if (requireChatLogin) {
        history.replace('/chat/validation/login');
      } else {
        openChatBeginStage(chatBeginMode);
      }
    } else {
      history.replace('/ticket/form');
    }

    dispatch(windowResize());

    return null;
  }
);

export const loadOptions = createAction('WIDGET_OPTIONS', options => $.extend(true, {}, options));
export const reloadOptions = createAction(
  'WIDGET_RELOAD_OPTIONS',
  options => (dispatch, getState) => {
    if (!options) {
      return;
    }

    const state = getState();
    const newOptions = Immutable.fromJS($.extend(true, {}, options));

    const buttonOptions = helpButtonSelector(state);
    const popupOptions = helpPopupSelector(state);
    const widgetPosition = widgetRawPositionSelector(state);
    const agentAcceptTimeout = agentAcceptTimeoutSelector(state);
    const agentPollingTimeout = agentPollingTimeoutSelector(state);

    dispatch(loadOptions(options));

    const buttonOptionsHaveChanged = !buttonOptions.equals(newOptions.get('button'));
    const popupOptionsHaveChanged = !popupOptions.equals(newOptions.getIn(['chat', 'popup']));
    const widgetPositionHasChanged = widgetPosition !== newOptions.getIn(['widget', 'position']);
    const chatWaitingTimeoutHasChanged = agentAcceptTimeout !== newOptions.getIn(['chat', 'waiting_timeout']);
    const onlineAgentPollingTimeoutHasChanged = agentPollingTimeout !== newOptions.getIn(['widget', 'agent_polling_timeout']);

    if (buttonOptionsHaveChanged || popupOptionsHaveChanged) {
      dispatch(closeWidget());
    } else if (!widgetPositionHasChanged && !chatWaitingTimeoutHasChanged && !onlineAgentPollingTimeoutHasChanged) {
      dispatch(openWidget());
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
