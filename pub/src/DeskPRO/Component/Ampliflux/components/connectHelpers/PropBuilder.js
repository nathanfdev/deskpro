export default class PropBuilder {
  constructor(propKey, builder) {
    this.propKey = propKey;
    this.checkFn = (val) => !!val;
    this.lastCheckedVal = undefined;
    this.lastIsLoadedCheck = false;
    this.initFn  = () => {
      throw new Error("No initFn for " + propKey);
      return undefined;
    }
  }

  /**
   * Sets a custom check function. The function is passed
   * the value of a property, and if you return true,
   * the property is considered loaded.
   *
   * @param {Function} fn
   */
  check(fn) {
    this.checkFn = fn;
    return this;
  }

  /**
   * Sets the check function to just check that the value is not null
   * and is not undefined.
   */
  isset() {
    this.checkFn = (val) => val !== null && typeof val !== 'undefined';
    return this;
  }

  /**
   * Sets the init method to be a dispatch of an action.
   *
   * @param {Function} action The action function to dispatch
   * @param {...} rest Any other params will be passed to the action function as-is
   */
  initWithAction(action, ...rest) {
    this.initFn = (dispatch) => {
      dispatch(action(...rest));
    }
    return this.builder;
  }

  /**
   * Sets the init method to a custom function. Your custom function
   * is passed the dispatch function.
   *
   * @param {Function} fn
   */
  initWith(fn) {
    this.initFn = (dispatch) => fn(dispatch);
    return this.builder;
  }

  _doInit(dispatch) {
    if (this.initFn) {
      this.initFn(dispatch);
    }
  }

  _isValLoaded(val) {
    if (typeof val === 'undefined') {
      this.lastIsLoadedCheck = false;
      return false;
    }

    if (val && val.then) {
      this.lastIsLoadedCheck = false;
      return false;
    }

    if (this.checkFn && !this.checkFn(val)) {
      this.lastIsLoadedCheck = false;
      return false;
    }

    this.lastIsLoadedCheck = true;
    return true;
  }
}
