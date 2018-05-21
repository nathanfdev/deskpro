import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';
import { store } from './store';
import * as dpWindowActions from '../Modules/Application/Actions/dpWindowActions';
import * as bootstrapActions from '../Modules/Application/Actions/bootstrapActions';
import * as chatActions from '../Modules/Chat/Actions/chatActions';
import { onlineAgentsSelector, onlineAgentsCountSelector } from '../Modules/Application/Selectors/peopleSelectors';
import { widgetHasChatSelector, widgetLoadedSelector } from '../Modules/Application/Selectors/bootstrap';
import { chatBeginModeSelector, widgetOpenedSelector } from '../Modules/Application/Selectors/dpWindow';
import { customChatFieldsOrderedSelector } from '../Modules/Application/Selectors/customFields';
import { history, getLocation } from './history';

// External window custom event handlers
// Called via 'receiveCustomEvent()' callback, can update the widget app state or send state info via 'dispatchCustomEvent' to the parent window

const handlers = {};
const getState = () => store.getState();
const dispatchCustomEvent = (type, options = {}) => {
  parent.postMessage({ type, options }, '*');
};

// public api handlers
// to communicate with external (parent) window
handlers.getWidgetStatus = () => {
  const state = getState();

  const loaded  = widgetLoadedSelector(state);
  const hasChat = widgetHasChatSelector(state);
  const onlineAgentsCount = onlineAgentsCountSelector(state);
  const isOpened = widgetOpenedSelector(state);

  dispatchCustomEvent('widgetStatus', {
    loaded,
    isOpened,
    chatAvailable: loaded && hasChat && onlineAgentsCount > 0
  });
};

handlers.getOnlineAgents = () => {
  const onlineAgents = onlineAgentsSelector(store.getState());
  dispatchCustomEvent('widgetOnlineAgents', Object.values(onlineAgents.toJS()));
};

handlers.openWidget = () => {
  const loaded = widgetLoadedSelector(getState());
  if (loaded) {
    store.dispatch(dpWindowActions.openWidget());
  }
};

// live demo handlers
// to set widget preview state
const dispatchChangedDemoStage = () => {
  // after some watcher changes we should sync live demo stage as well
  const state = getState();
  const widgetOpened = widgetOpenedSelector(state);
  if (!widgetOpened) {
    dispatchCustomEvent('widgetDemoStage', 'button');
  } else {
    getLocation((location) => {
      if (location.pathname === '/ticket/form') {
        dispatchCustomEvent('widgetDemoStage', 'ticket');
      } else if (location.pathname === '/chat/active') {
        dispatchCustomEvent('widgetDemoStage', 'in-chat');
      } else if (location.pathname.indexOf('/chat/begin') !== -1) {
        dispatchCustomEvent('widgetDemoStage', 'pre-chat');
      }
    });
  }
};

handlers.reloadLiveDemoOptions = (options) => {
  store.dispatch(dpWindowActions.reloadOptions(options));
  dispatchChangedDemoStage();
};
handlers.reloadLiveDemoSettings = (options) => {
  store.dispatch(bootstrapActions.reloadSettings(options));
  dispatchChangedDemoStage();
};

handlers.changeLiveDemoStage = (stage) => {
  const state = getState();

  switch (stage) {
    case 'button':
      history.replace('/');
      store.dispatch(dpWindowActions.closeWidget());
      break;
    case 'pre-chat':
      history.replace(`/chat/begin/${chatBeginModeSelector(state)}`);
      store.dispatch(dpWindowActions.reopenWidget());
      break;
    case 'in-chat':
      history.replace('/chat/active');
      store.dispatch(dpWindowActions.reopenWidget());
      break;
    case 'ticket':
      history.replace('/ticket/form');
      store.dispatch(dpWindowActions.reopenWidget());
      break;
    default:
      break;
  }
};
handlers.setLiveDemoSampleState = (state) => {
  const { sampleState = {}, options = {}, settings = {}, chatCustomFields = {} } = state;
  const { agents = [], users = [] } = sampleState.people || {};
  const { departments = [], currencies = [] } = sampleState;
  const { dispatch } = store;

  // set global state
  dispatch(dpWindowActions.loadOptions(options));
  dispatch(bootstrapActions.setSettings(settings));

  // set person state
  dispatch(setCollection('Person', 'onlineAgents', Immutable.fromJS(agents)));
  dispatch(setCollection('Person', 'all', Immutable.fromJS(agents.concat(users))));
  dispatch(setCollection('ChatDepartment', 'all', Immutable.fromJS(departments)));
  dispatch(setCollection('ChatDepartment', 'online', Immutable.fromJS(departments)));
  dispatch(setCollection('Currency', 'all', Immutable.fromJS(currencies)));

  // set chat state
  dispatch(chatActions.setLoaded());
  dispatch(chatActions.updateChatInfo(sampleState.chat.info));
  dispatch(chatActions.addNewMessages(sampleState.chat.messages));
  dispatch(setCollection('CustomDefChat', 'all', Immutable.fromJS(chatCustomFields)));
};
handlers.setLiveDemoChatCustomFields = (customFields) => {
  const oldState = getState();
  const oldCustomFields = customChatFieldsOrderedSelector(oldState);

  store.dispatch(setCollection('CustomDefChat', 'all', Immutable.fromJS(customFields)));

  const newState = getState();
  const newCustomFields = customChatFieldsOrderedSelector(newState);

  if (!newCustomFields.equals(oldCustomFields)) {
    handlers.changeLiveDemoStage('pre-chat');
  }
};

window.addEventListener('message', (event) => {
  const { type, options } = event.data;
  if (handlers[type]) {
    handlers[type](options);
  }
}, false);

// Internal widget api
// Public actions that could be triggered in the widget app

export const dispatchWidgetStatus = () => {
  handlers.getWidgetStatus();
};

export const dispatchOnlineAgents = () => {
  handlers.getOnlineAgents();
};
