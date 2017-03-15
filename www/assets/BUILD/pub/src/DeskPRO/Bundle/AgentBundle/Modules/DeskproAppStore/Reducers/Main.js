import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/Actions';
import Immutable from 'immutable';

const initialState = Immutable.fromJS({
  'appconfig': null,
  'contexts': {}
});

/**
 * @param {Object} state
 * @param {Object} payload
 * @param {Object} action
 * @returns {Object}
 */
function loadAppConfigHandler(state, payload, action) {
  switch(action.meta.sequence) {
    case 'done' :
      return state.set('appconfig', Immutable.fromJS(payload));
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
    [actions.loadAppConfig]: loadAppConfigHandler,
    [actions.appMounted]: appMountedHandler,
    [actions.appContextCreated]: appContextCreatedHandler,
  }
);
