import { isDSA, getActionType } from '../actions/actionUtils';
import ReducerBuilder from "./utils/ReducerBuilder";

/**
 * Creates a reducer using a builder passed to your buildFn.
 *
 * <code>
 * export const myStore = createReducer(r => {
 *   r.initialState({
 *     foo: "bar"
 *   });
 *
 *   r.action("MY_ACTION", (state, data) => {
 *     return {
 *       ...state,
 *       foo: data.something.foo
 *     }
 *   });
 * });
 * </code>
 *
 * @see ReducerBuilder for more examples
 * @param {Function} buildFn   `buildFn(r)` your function that accepts a ReducerBuilder.
 * @return {Function}
 */
export default function createReducer(buildFn) {
  const builder = new ReducerBuilder();
  buildFn(builder);

  const initialState = builder.getInitialState();
  const handlersMap  = builder.getHandlersMap();

  return (state = initialState, action) => {
    if (isDSA(action)) {
      const type = getActionType(action);
      if (typeof handlersMap[type] !== 'undefined') {
        return handlersMap[action.type](state, action.payload, action);
      }
    }

    return state;
  }
};
