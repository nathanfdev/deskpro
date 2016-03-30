import { store } from './store';
import * as dpWindowActions from '../Modules/Application/Actions/dpWindowActions';
import * as bootstrapActions from '../Modules/Application/Actions/bootstrapActions';
import { onlineAgentsSelector } from '../Modules/Application/Selectors/peopleSelectors';

function postMessage(type, options = {}) {
  parent.postMessage({ type, options }, '*');
}

export const widgetLoaded = () => {
  postMessage('widgetLoaded');
};

export const reloadOptions = (options) => {
  store.dispatch(dpWindowActions.reloadOptions(options));
};

export const reloadSettings = (options) => {
  store.dispatch(bootstrapActions.reloadSettings(options));
};

export const getOnlineAgents = () => {
  const onlineAgents = onlineAgentsSelector(store.getState());
  postMessage('widgetOnlineAgents', Object.values(onlineAgents.toJS()));
};

export const openWidget = () => {
  store.dispatch(dpWindowActions.openWidget());
};

const receiveMessage = event => {
  const { type, options } = event.data;
  const action = exports[type];

  if (action) {
    action(options);
  }
};

window.addEventListener('message', receiveMessage, false);
