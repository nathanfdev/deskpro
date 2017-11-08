import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import {
  /** var {Context} **/ Context
} from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';

import * as actions from '../Actions/Actions';

const initialState = Immutable.fromJS({
  apps:     null, // all loaded apps (instances)
  widgets:  null, // configuration for all instances
  contexts: {},
  apiToken: null,
  config:   {},
});

/**
 * @param {Object} state
 * @param {{config:AppsConfig, manifests:*}}  payload
 * @param {Object} action
 * @returns {Object}
 */
function loadAppstoreConfigHandler(state, payload, action) {
  const { sequence } = action.meta;
  if (sequence !== 'done') { return state; }
  // let's assume it was validated before is was serialized
  const { config } = payload;

  const changes = Immutable.fromJS({ config });
  return state.merge(changes);
}

/**
 * @param {Object} state
 * @param {{config:AppsConfig, manifests:*}}  payload
 * @param {Object} action
 * @returns {Object}
 */
function loadAppsHandler(state, payload, action) {
  const { sequence } = action.meta;
  if (sequence !== 'done') { return state; }

  /** @var {AppsConfig} */
  const { config, manifests } = payload;
  const { environment } = config;

  const newState = Immutable.fromJS({ apps: { environment, manifests } });
  return state.merge(newState);
}

/**
 * @param {Object} state
 * @param {{config:AppsConfig, contexts: Array<Context>}} payload
 * @param {Object} action
 * @returns {Object}
 */
function unloadContexts(state, payload, action) { // eslint-disable-line no-unused-vars
  const { contexts } = payload;
  if (contexts.length === 0) {
    return state;
  }

  const keys = contexts.map(context => context.id);
  const newContexts = state.get('contexts').filter((value, key) => keys.indexOf(key) < 0);
  return state.set('contexts', newContexts);
}

/**
 * @param {Object} state
 * @param {{config:AppsConfig, contexts: Array<Context>}} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadContexts(state, payload, action) { // eslint-disable-line no-unused-vars
  const { config, contexts: contextProps } = payload;
  if (contextProps.length === 0) { return state; }

  const mergeContextProps = contextProp => Object.assign({}, { ...contextProp }, { ...config.toJS('apps') });
  const createContextMap = (acc, context) => { acc[context.id] = context; return acc; };

  const newContexts = contextProps.map(mergeContextProps).map(Context.fromProps).reduce(createContextMap, {});
  const allContexts = state.get('contexts').merge(newContexts);
  return state.set('contexts', allContexts);
}

function loadApiTokenHandler(state, payload, action) {
  const { sequence } = action.meta;
  if (sequence !== 'done') { return state; }

   // the deskpro appstore config is also available in the payload if we need
  // /** @var {AppsConfig} */
  // const { config, token } = payload;
  const { token } = payload;

  if (token) {
    const newState = Immutable.fromJS({ apiToken: token });
    return state.merge(newState);
  }

  return state;
}

export default createReducer(
  initialState,
  {
    [actions.loadContextsFromPageFragments]:   loadContexts,
    [actions.unloadContextsFromPageFragments]: unloadContexts,
    [actions.loadApps]:                        loadAppsHandler,
    [actions.loadApiToken]:                    loadApiTokenHandler,
    [actions.loadAppstoreConfig]:              loadAppstoreConfigHandler
  }
);
