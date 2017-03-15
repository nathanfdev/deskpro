import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/Actions';
import Immutable from 'immutable';
import DeskproApp from '../Services/DeskproApp';

const initialState = Immutable.fromJS({
  'apps': null, //all loaded apps
  'instances': null, //configuration for all instances
  'contexts': {}
});

/**
 * @param {Object} state
 * @param {Array} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadAppsHandler(state, payload, action) {
  switch(action.meta.sequence) {
    case 'done' :
    const newState = Immutable.fromJS({ apps: payload, instances:  DeskproApp.instancesFromApplicationJSList(payload) });
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
      //console.log('intercepted app mounted action', payload);
      return state;
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
function appContextCreatedHandler(state, payload, action) {
  switch(action.meta.sequence) {
    case 'done' :
      const contexts = state.get('contexts').set(payload.id, payload);
      return state.set('contexts', contexts);
    default:
      return state;
  }
}

export default createReducer(
  initialState,
  {
    [actions.appMounted]: appMountedHandler,
    [actions.appContextCreated]: appContextCreatedHandler,
    [actions.loadApps]: loadAppsHandler
  }
);
