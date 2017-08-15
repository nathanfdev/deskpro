import cloneDeep from 'lodash/cloneDeep';
import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import * as actions from '../Actions/Actions';

const initialState = Immutable.fromJS({
  apps:     null, // all loaded apps (instances)
  widgets:  null, // configuration for all instances
  contexts: {}
});

/**
 * @param {Object} state
 * @param {Array} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadDevAppHandler(state, payload, action) {
  switch (action.meta.sequence) {
    case 'done': {
      const newState = Immutable.fromJS({
        apps: { environment: 'development', manifests: [payload] }
      });
      return state.merge(newState);
    }
    default:
      return state;
  }
}

/**
 * @param {Object} state
 * @param {Array} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadAppsHandler(state, payload, action) {
  switch (action.meta.sequence) {
    case 'done': {
      const manifests = payload.data.map((instance) => {
        const appId = instance.app;
        const manifest = cloneDeep(payload.linked.app[appId].manifest);

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
    default:
      return state;
  }
}

/**
 * @param {Object} state
 * @param {Object} payload
 * @param {Object} action
 * @returns {Object}
 */
function appMountedHandler(state, payload, action) {
  switch (action.meta.sequence) {
    case 'done':
      return state;
    default:
      return state;
  }
}

/**
 * @param {Object} state
 * @param {Array<Context>} contextList
 * @param {Object} action
 * @returns {Object}
 */
function loadPageFragmentAppsHandler(state, contextList, action) { // eslint-disable-line no-unused-vars
  if (contextList.length === 0) { return state; }

  const newContexts = contextList.reduce((acc, context) => { acc[context.id] = context; return acc; }, {});
  const allContexts = state.get('contexts').merge(newContexts);
  return state.set('contexts', allContexts);
}


export default createReducer(
  initialState,
  {
    [actions.loadPageFragmentApps]: loadPageFragmentAppsHandler,
    [actions.appMounted]:           appMountedHandler,
    [actions.loadApps]:             loadAppsHandler,
    [actions.loadDevApp]:           loadDevAppHandler
  }
);
