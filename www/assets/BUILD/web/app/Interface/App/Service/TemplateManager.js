/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let TemplateManager;
  return (TemplateManager = class TemplateManager {
    constructor(TemplateLoader, $templateCache, $q) {
      this.TemplateLoader = TemplateLoader;
      this.$templateCache = $templateCache;
      this.$q = $q;
      this.pending = [];
      this.pendingNames = {};
      this.sendPending = {};
    }

    commonName(view) { return view; }

    /*
     * Sets a template in the template cache
     *
     * @param {String} view
     * @param {String} source
     */
    setTemplate(view, source) {
      view = this.commonName(view);
      return this.$templateCache.put(view, source);
    }


    /*
     * Removes a template from the cache
     *
     * @param {String} view
     */
    removeTemplate(view) {
      return this.$templateCache.remove(this.commonName(view));
    }


    /*
     * Execute the pending loads by ending the http request.
     *
     * @return {promise}
     */
    loadPending() {
      let v;
      if (!this.pending.length) {
        const d = this.$q.defer();
        d.resolve();
        return d.promise;
      }

      const preloadTpls = this.TemplateLoader.load(this.pending);

      this.pending = [];
      this.sendPending = this.pendingNames;
      this.pendingNames = {};

      for (var k of Object.keys(this.sendPending || {})) {
        v = this.sendPending[k];
        this.sendPending[k] = preloadTpls;
      }

      preloadTpls.then( data => {
        for (let tpl of Array.from(data)) {
          this.$templateCache.put(tpl.id, tpl.source);
        }

        return (() => {
          const result = [];
          for (k of Object.keys(this.sendPending || {})) {
            v = this.sendPending[k];
            if (v === preloadTpls) {
              this.sendPending[k] = null;
              result.push(delete this.sendPending[k]);
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      });

      return preloadTpls;
    }


    /*
     * Gets the template source if it is already loaded, or null if it isnt
     *
     * @return {String|null}
     */
    getNow(view) {
      view = this.commonName(view);
      return this.$templateCache.get(view) || null;
    }

    /*
     * an alias
     */
    get(view) {
      return this.queue(view);
    }

    /*
    * Mark a view to be loaded next time we are loading templates
      *
      * @param {String} view
    */
    load(view) {
      view = this.commonName(view);
      if (!this.$templateCache.get(view) && !this.pendingNames[view] && !this.sendPending[view]) {
        this.pending.push(view);
        return this.pendingNames[view] = true;
      }
    }

    /*
     * Loads a template source along with any others that are queued.
     *
     * @return {promise}
     */
    queue(view) {
      let d;
      view = this.commonName(view);
      const exist = this.$templateCache.get(view);
      if (exist || (exist === "")) {
        d = this.$q.defer();
        d.resolve(this.$templateCache.get(view));
        return d.promise;
      }

      if (this.sendPending[view]) {
        d = this.$q.defer();
        this.sendPending[view].then(() => {
          return d.resolve(this.$templateCache.get(view));
        });
        return d.promise;
      }

      const promise = this.loadPending();
      const defer = this.$q.defer();

      promise.then(() => {
        const tpl = this.$templateCache.get(view);
        if (tpl || (tpl === "")) {
          return defer.resolve(tpl);
        } else {
          console.log("Failed to load %s", view);
          return defer.reject("failed");
        }
      });

      return defer.promise;
    }
  });
});