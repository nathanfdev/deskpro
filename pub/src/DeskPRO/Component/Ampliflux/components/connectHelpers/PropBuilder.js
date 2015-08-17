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

  check(fn) {
    this.checkFn = fn;
    return this;
  }

  isset() {
    this.checkFn = (val) => !!val;
    return this;
  }

  initWithAction(action, ...rest) {
    this.initFn = (dispatch) => {
      dispatch(action(...rest));
    }
    return this.builder;
  }

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
