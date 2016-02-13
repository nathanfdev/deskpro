import { getActionType } from '../actions/actionUtils';
import Immutable from 'immutable';

/**
 * Create a new reducer.
 *
 * Handlers created through this function are passed the following params:
 * - {Immuatable.Map}           state    The current state
 * - {any}                      payload  The action payload, whatever that is. Typically maps/arrays will be Immutable.
 * - {Object}                   action   The full action. Note that action.payload === payload.
 *
 * @param {Object} initialState  The initial state
 * @param {Map}    handlerGroups A map of actionType => handlerFn
 * @returns {Function} Your reducer
 */
export function createReducer(initialState, ...handlerGroups) {
  const handlers = {};
  handlerGroups.forEach(g => Object.assign(handlers, g));

  return function reducer(state = initialState, action = {}) {
    const actionType = getActionType(action, true);
    const inState = Immutable.Iterable.isIterable(state) ? state : Immutable.fromJS(state);

    let newState = inState;

    if (actionType && handlers.hasOwnProperty(actionType)) {
      const payload = action.payload || undefined;
      newState = handlers[actionType](state, payload, action);
    }

    if (!Immutable.Iterable.isIterable(newState)) {
      console.error('Reducers must return Immutable objects', state);
      throw new TypeError('Reducers must return Immutable objects');
    }

    return newState;
  };
}
