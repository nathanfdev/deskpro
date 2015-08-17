import { combineReducers } from "redux";

function getReducer(val, builderFn) {
  if (typeof val === 'function') {
    if (builderFn) {
      return builderFn(val);
    } else {
      return val;
    }
  } else {
    return combineReducerHierarchy(val, builderFn);
  }
}

/**
 * Given a hierarchy of reducers, combine them into
 * a single reducer.
 *
 * <code>
 * const reducer = combineReducerHierarchy({
 *   "Some": {
 *     "nested": {
 *       "structure": function() { ... }
 *     }
 *   }
 * });
 * </code>
 *
 * @param {Object}   reducersObj  The hierarchy to combine
 * @param {Function} builderFn    An optional function that is called on every reducer to build it. It must return the visitor or null to cancel adding it.
 * @return {Function}
 */
export default function combineReducerHierarchy(reducersObj, builderFn) {
  let finalMap = {};
  for (let [name, val] of Object.entries(reducersObj)) {
    const r = getReducer(val, builderFn);
    if (r) {
      finalMap[name] = r;
    }
  }

  return combineReducers(finalMap);
}
