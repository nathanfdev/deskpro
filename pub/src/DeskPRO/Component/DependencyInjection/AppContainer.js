/*
 This is a simple object used to set the container
 so it can be imported by scripts (like react) that
 dont use an IoC container or offer a way for it to work.

 Typically you'll add a line like this:

     import App from "DeskPRO/Component/DependencyInjection/AppContainer";

 And use it like so:

     App.get('http').sendGet(...);


*/

export default {
  setContainer: function(container) {
    this.container = container;
  },

  getContainer() {
    return this.container;
  },

  get: function(name) {
    return this.container.get(name);
  },

  invoke: function(fn, self = null, locals = null) {
    return this.container.invoke(fn, self, locals);
  },

  invokeConstructor: function(fn, locals = null) {
    return this.container.invokeConstructor(fn, locals);
  },

  getNames: function(names, locals = null) {
    return this.container.getNames(names, locals);
  },

  has: function(name) {
    return this.container.has(name);
  }
};