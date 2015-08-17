import PropBuilder from "./PropBuilder";

export default class Builder {
  constructor() {
    this.selectFn = (state) => state;
    this.selectFn.isDefault = true;

    this.dispatchToProps = null;
    this.propReqs = [];
    this.allowLoadStateChange = false;
  }

  select(fn) {
    this.selectFn = fn;
    return this;
  }

  prop(propKey) {
    const p = new PropBuilder(propKey, this);
    this.propReqs.push(p);
    return p;
  }

  enableReloadState() {
    this.allowLoadStateChange = true;
  }
}
