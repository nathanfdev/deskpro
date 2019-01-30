/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let StateCollection;
  return (StateCollection = class StateCollection {
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
  });
});