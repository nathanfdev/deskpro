export default class AsyncHandlerBuilder {
  constructor() {
    this.startFn   = null;
    this.successFn = null;
    this.errorFn   = null;
    this.doneFn    = null;
  }

  start(fn) {
    this.startFn = fn;
    return this;
  }

  success(fn) {
    this.successFn = fn;
    return this;
  }

  error(fn) {
    this.errorFn = fn;
    return this;
  }

  done(fn) {
    this.doneFn = fn;
    return this;
  }
}
