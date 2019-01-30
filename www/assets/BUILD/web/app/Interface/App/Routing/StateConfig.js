// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let StateConfig;
  return (StateConfig = class StateConfig {
    static createFactory(module) {
      return function(id = null) {
        const c = new StateConfig(module, id);
        return c;
      };
    }

    constructor(module, id = null, url, ctrl = null, tpl = null, resolve = null, options = null) {
      if (module == null) { module = ''; }
      this.module = module;
      this.id = id;
      if (url == null) { url = ''; }
      this.url = url;
      this.ctrl = ctrl;
      this.tpl = tpl;
      this.resolve = resolve;
      this.options = options;
    }
    setModule(module) { this.module = module; return this; }
    setId(id) { this.id = id; return this; }
    setUrl(url) { this.url = url; return this; }
    setCtrl(ctrl) { this.ctrl = ctrl; return this; }
    setTpl(tpl) { this.tpl = tpl; return this; }
    setResolve(resolve) { this.resolve = resolve; return this; }
    setOptions(options) { this.options = options; return this; }
    setAbstract() {
      this.is_abstract = true;
      return this;
    }

    applyToStateProvider($stateProvider) {
      const resolve = this.resolve || {};
      const options = this.options || {};

      const m = this.module;

      resolve.loadModule = ['$ocLazyLoad', $ocLazyLoad => $ocLazyLoad.load(m)
      ];

      options.url         = this.url;
      options.templateUrl = this.tpl;
      options.resolve     = resolve;
      options.controller  = this.ctrl;

      if (this.isAbstract) {
        options.abstract = true;
      }

      return $stateProvider.state(this.id, options);
    }
  });
});
