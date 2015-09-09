import { getActionType } from '../actions/actionUtils';
import Immutable from "immutable";

/**
 * Create a new reducer.
 *
 * Handlers created through this function are passed the following params:
 * - {Immuatable.Map}           state    The current state
 * - {any}                      payload  The action payload, whatever that is. Typically maps/arrays will be Immutable.
 * - {Object}                   action   The full action. Note that action.payload === payload.
 *
 * @param {Object} initialState  The initial state
 * @param {Map}    handlers      A map of actionType => handlerFn
 * @returns {Function}
 */
export default function createReducer(initialState, handlers = {}) {
  return function reducer(state = initialState, action = {}) {
    const actionType = getActionType(action, true);

    if (!Immutable.Iterable.isIterable(state)) {
      state = Immutable.fromJS(state);
    }

    if (actionType && handlers.hasOwnProperty(actionType)) {
      let payload = action.payload || undefined;
      state = handlers[actionType](state, payload, action);
    } else {
      state = state;
    }

    if (!Immutable.Iterable.isIterable(state)) {
      console.error('Reducers must return Immutable objects', state);
      throw new TypeError('Reducers must return Immutable objects');
    }

    return state;
  }
}
