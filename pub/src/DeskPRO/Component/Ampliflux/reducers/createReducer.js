import { getActionType } from '../actions/actionUtils';
import Immutable from "immutable";

/**
 * Create a new reducer.
 *
 * Handlers created through this function are passed the following params:
 * - {Immuatable.Map}           state    The current state
 * - {any}                      payload  The action payload, whatever that is. Typically maps/arrays will be Immutable.
 * - {Immuatable.Map|Object}    action   The full action. Note that action.payload === payload. Typically this is an Immutable.Map.
 *
 * @param {Object} initialState  The initial state
 * @param {Map}    handlers      A map of actionType => handlerFn
 * @returns {Function}
 */
export default function createReducer(initialState, handlers = {}, enforceImmutable = true) {
  return function reducer(state = initialState, action) {
    const actionType = getActionType(action, true);

    if (!Immutable.Iterable.isIterable(state)) {
      state = Immutable.fromJS(state);
    }

    if (actionType && handlers.hasOwnProperty(actionType)) {
      if (Immutable.Map.isMap(action)) {
        state = handlers[actionType](state, action.get('payload'), action);
      } else {
        state = handlers[actionType](state, action.payload || undefined, action);
      }
    } else {
      state = state;
    }

    if (enforceImmutable && !Immutable.Iterable.isIterable(state)) {
      console.error('Reducers must return Immutable objects', state);
      throw new TypeError('Reducers must return Immutable objects');
    }

    return state;
  }
}
