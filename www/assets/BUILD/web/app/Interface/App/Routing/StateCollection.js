// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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