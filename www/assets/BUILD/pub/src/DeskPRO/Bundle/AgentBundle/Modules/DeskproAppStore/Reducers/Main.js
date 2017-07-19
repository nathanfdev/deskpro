import cloneDeep from 'lodash/lang/cloneDeep';
import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import * as actions from '../Actions/Actions';
import { Context } from '../Domain/Context';

const initialState = Immutable.fromJS({
  apps:     null, // all loaded apps (instances)
  widgets:  null, // configuration for all instances
  contexts: {}
});


/**
 * @param {Object} state
 * @param {{config:DeskproAppStoreConfiguration, manifests:*}}  payload
 * @param {Object} action
 * @returns {Object}
 */
function loadAppsHandler(state, payload, action) {
  const { sequence } = action.meta;
  if (sequence !== 'done') { return state; }

  /** @var {DeskproAppStoreConfiguration} */
  const { config, manifests: manifestsJS } = payload;

  if (config.environment === 'production') {
    const manifests = manifestsJS.data.map((instance) => {
      const appId = instance.app;
      const manifest = cloneDeep(manifestsJS.linked.app[appId].manifest);

      manifest.application_id = instance.application_id;
      manifest.id = instance.id;
      manifest.targets = instance.targets;
      manifest.title = manifest.name;

      return manifest;
    });

    const newState = Immutable.fromJS({
      apps: { environment: 'production', manifests }
    });
    return state.merge(newState);
  }

  if (config.environment === 'development') {
    const newState = Immutable.fromJS({
      apps: { environment: 'development', manifests: [manifestsJS] }
    });
    return state.merge(newState);
  }

  return state;
}

/**
 * @param {Object} state
 * @param {{config:DeskproAppStoreConfiguration, contexts: Array<Context>}} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadPageFragmentAppsHandler(state, payload, action) { // eslint-disable-line no-unused-vars
  const { config, contexts: contextProps } = payload;
  if (contextProps.length === 0) { return state; }

  const mergeContextProps = contextProp => Object.assign({}, { ...contextProp }, { ...config.toJS('apps') });
  const createContextMap = (acc, context) => { acc[context.id] = context; return acc; };

  const newContexts = contextProps.map(mergeContextProps).map(Context.fromProps).reduce(createContextMap, {});
  const allContexts = state.get('contexts').merge(newContexts);
  return state.set('contexts', allContexts);
}


export default createReducer(
  initialState,
  {
    [actions.loadPageFragmentApps]: loadPageFragmentAppsHandler,
    [actions.loadApps]:             loadAppsHandler,
  }
);
