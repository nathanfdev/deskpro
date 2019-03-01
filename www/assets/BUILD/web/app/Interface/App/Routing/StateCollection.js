define(function() {
  class StateCollection {
    constructor(factory) {
      this.factory = factory;
      this.routes = [];
      this.whens = [];
    }

    add(id) {
      const r = this.factory(id);
      this.routes.push(r);
      return r;
    }

    when(path, to_path) {
      this.whens.push([path, to_path]);
      return null;
    }
  }
  return StateCollection;
});
