import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/Actions';
import Immutable from 'immutable';
import DeskproAppStore from '../DeskproAppStore';

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
    const newState = Immutable.fromJS({
      apps: { environment: 'production', manifests: payload }
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
 * @param {Map} payload the container's context
 * @param {Object} action
 * @return {*}
 */
function mountPageFragmentContainersHandler(state, payload, action)
{
  for (const context of payload.values()) {
    if (context.has('page')) { //page fragment
      const routeUrl = context.get('page').get('routeUrl');
      const pageTab = DeskPRO_Window.TabBar.findTabByRouteUrl(routeUrl);
      if (pageTab) {
        DeskproAppStore.asyncLoadPageFragment(context, pageTab.page);
      } else {
        // TODO handle closing of tabs, unmounting of components
        console.log('found a context (tab) which was closed without any cleanup actions executed afterwards. please fix this');
      }
    }
  }

  return state;
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
    [actions.mountPageFragmentContainers] : mountPageFragmentContainersHandler,
    [actions.loadPageFragmentApps]: loadPageFragmentAppsHandler,
    [actions.appMounted]: appMountedHandler,
    [actions.loadApps]: loadAppsHandler,
    [actions.loadDevApp]: loadDevAppHandler
  }
);
