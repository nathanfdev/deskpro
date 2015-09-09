import { getActionType } from '../actions/actionUtils';
import createReducer from "./createReducer";
import Immutable from "immutable";

export const composeReducers = (...funcs) => (state, action) => funcs.reduceRight((composed, f) => f(composed, action), state);

/**
 * This wraps a reducer to make it a "shared records store".
 *
 * These stores are designed to be used to store data used by many components.
 * For example, users or agents.
 *
 * @param {Object} initialState  The initial state
 * @param {Map}    handlers      A map of actionType => handlerFn
 * @returns {Function}
 */
export default function sharedRecordsStore(state, action) {

}
