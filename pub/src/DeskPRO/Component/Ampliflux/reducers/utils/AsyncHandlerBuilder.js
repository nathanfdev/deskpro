/**
 * Builder used with ReducerBuilder that helps you specify the various handlers
 * in response to an async action.
 *
 * @see ReducerBuilder
 */
export default class AsyncHandlerBuilder {
  constructor() {
    this.startFn   = null;
    this.successFn = null;
    this.errorFn   = null;
    this.doneFn    = null;
  }

  /**
   * The start action is dispatched as soon as your action is dispatched.
   * One typical use-case is to show a loading indicator.
   *
   * Under the hood, this is attached to the ACTION_NAME.START action type.
   *
   * @param {Function} fn
   */
  start(fn) {
    this.startFn = fn;
    return this;
  }

  /**
   * The success action is dispatched once your promise resolves.
   *
   * Under the hood, this is attached to the ACTION_NAME action type.
   *
   * @param {Function} fn
   */
  success(fn) {
    this.successFn = fn;
    return this;
  }

  /**
   * The error action is dispatched if your promise rejects.
   *
   * Under the hood, this is attached to the ACTION_NAME.ERROR action type.
   *
   * @param {Function} fn
   */
  error(fn) {
    this.errorFn = fn;
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
  done(fn) {
    this.doneFn = fn;
    return this;
  }
}
