import { store } from './store';
import * as dpWindowActions from '../Modules/Application/Actions/dpWindowActions';
import * as bootstrapActions from '../Modules/Application/Actions/bootstrapActions';
import { onlineAgentsSelector, onlineAgentsCountSelector } from '../Modules/Application/Selectors/peopleSelectors';
import { widgetHasChatSelector, widgetLoadedSelector } from '../Modules/Application/Selectors/bootstrap';

// External window custom event handlers
// Called via 'receiveCustomEvent()' callback, can update the widget app state or send state info via 'dispatchCustomEvent' to the parent window

const handlers = {};
const getState = () => store.getState();
const dispatchCustomEvent = (type, options = {}) => {
  parent.postMessage({ type, options }, '*');
};

handlers.reloadOptions = (options) => store.dispatch(dpWindowActions.reloadOptions(options));
handlers.reloadSettings = (options) => store.dispatch(bootstrapActions.reloadSettings(options));

handlers.getWidgetStatus = () => {
  const state = getState();

  const loaded  = widgetLoadedSelector(state);
  const hasChat = widgetHasChatSelector(state);
  const onlineAgentsCount = onlineAgentsCountSelector(state);

  dispatchCustomEvent('widgetStatus', {
    loaded,
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

window.addEventListener('message', event => {
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
