import Immutable from "immutable";

/**
 * Combines two handler functions into one.
 *
 * @param {Function} fn
 * @param {Function} fn2
 * @return {Function}
 */
function handlerFunctions(fn, fn2) {
  if (!fn) {
    return fn2;
  }
  if (!fn2) {
    return fn;
  }

  return (state, ...rest) => fn(fn2(state, ...rest), ...rest);
}

/**
 * Builder used with ReducerBuilder that helps you specify the various handlers
 * in response to an async action.
 *
 * @see ReducerBuilder
 */
export default class AsyncHandlerBuilder {
  constructor() {
    this.startFn      = null;
    this.successFn    = null;
    this.errorFn      = null;
    this.doneFn       = null;
  }

  /**
   * Sets `isLoading` state automatically for start/done
   */
  handleLoading(propName = 'isLoading') {
    propName = propName.split('.');
    this.handleStart((state) => {
      if (!Immutable.Map.isMap(state)) {
        state = Immutable.Map(state);
      }

      state.setIn(propName, true);
    });
    this.handleDone((state) => {
      if (!Immutable.Map.isMap(state)) {
        state = Immutable.Map(state);
      }

      state.setIn(propName, false);
    });
    return this;
  }

  /**
   * The start action is dispatched as soon as your action is dispatched.
   * One typical use-case is to show a loading indicator.
   *
   * Under the hood, this is attached to the ACTION_NAME.START action type.
   *
   * @param {Function} fn
   */
  handleStart(fn) {
    this.startFn = handlerFunctions(this.startFn, fn);
    return this;
  }

  /**
   * The success action is dispatched once your promise resolves.
   *
   * Under the hood, this is attached to the ACTION_NAME action type.
   *
   * @param {Function} fn
   */
  handleSuccess(fn) {
    this.successFn = handlerFunctions(this.successFn, fn);
    return this;
  }

  /**
   * The error action is dispatched if your promise rejects.
   *
   * Under the hood, this is attached to the ACTION_NAME.ERROR action type.
   *
   * @param {Function} fn
   */
  handleError(fn) {
    this.errorFn = handlerFunctions(this.errorFn, fn);
    return this;
  }

  /**
   * The done action is dispatched once your promise resolves OR rejects.
   * The action payload will be whatever result was resolved or rejected.
   * Hint: You can always tell an error because action.error will be true.
   *
   * Under the hood, this is attached to the ACTION_NAME.DONE action type.
   *
   * @param {Function} fn
   */
  handleDone(fn) {
    this.doneFn = handlerFunctions(this.doneFn, fn);
    return this;
  }
}
