import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/Actions';
import Immutable from 'immutable';

const initialState = Immutable.fromJS({
  'apps': null, //all loaded apps (instances)
  'widgets': null, //configuration for all instances
  'contexts': {}
});

/**
 * @param {Object} state
 * @param {Array} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadDevAppHandler(state, payload, action) {
  switch(action.meta.sequence) {
    case 'done' :
    const newState = Immutable.fromJS({
      apps: { environment: 'development', manifests: [payload] }
    });
    return state.merge(newState);
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
  switch(action.meta.sequence) {

    case 'done' :

    const manifests = payload.data.map( instance => {
      const { app } = instance;
      const { manifest } = payload.linked.app[app];

      manifest['application_id'] = instance['application_id'];
      manifest['id'] = instance['id'];
      manifest['targets'] = instance['targets'];
      manifest['title'] = manifest['name'];

      return manifest;
    });


    const newState = Immutable.fromJS({
      apps: { environment: 'production', manifests }
    });
    return state.merge(newState);
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
  switch(action.meta.sequence) {
    case 'done' :
      return state;
    default:
      return state;
  }
}

/**
 * @param {Object} state
 * @param {Object} contextJS
 * @param {Object} action
 * @returns {Object}
 */
function loadPageFragmentAppsHandler(state, contextJS, action) {
  const newContext = Immutable.fromJS (contextJS);
  const allContexts = state.get('contexts').set(contextJS.page.pageUid, newContext);
  return state.set('contexts', allContexts);
}


export default createReducer(
  initialState,
  {
    [actions.loadPageFragmentApps]: loadPageFragmentAppsHandler,
    [actions.appMounted]: appMountedHandler,
    [actions.loadApps]: loadAppsHandler,
    [actions.loadDevApp]: loadDevAppHandler
  }
);
