import PropBuilder from "./PropBuilder";

export default class Builder {
  constructor() {
    this.selectFn = (state) => state;
    this.selectFn.isDefault = true;

    this.dispatchToProps = null;
    this.propReqs = [];
    this.allowLoadStateChange = false;
  }

  /**
   * Specify the state selector. The value of this will be made available
   * to your component via props. This is the same as Redux's own connect.
   *
   * @param {Function} fn
   */
  select(fn) {
    this.selectFn = fn;
    return this;
  }

  /**
   * Specify a property to check as part of dependency checking.
   * A new PropBuilder will be returned where you can specify checks
   * and how to init the value if it's not set.
   *
   * @param {String} propKey The property key. Use dots to separate hierarchy.
   * @return {PropBuilder}
   */
  prop(propKey) {
    const p = new PropBuilder(propKey, this);
    this.propReqs.push(p);
    return p;
  }

  /**
   * When enabled, the state can be turned back to unloaded
   * (for example, if you are reloading something).
   */
  enableReloadState() {
    this.allowLoadStateChange = true;
  }
}
