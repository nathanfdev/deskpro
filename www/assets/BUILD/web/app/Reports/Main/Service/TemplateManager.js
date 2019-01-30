// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  class Reports_Main_Service_TemplateManager {
    constructor($templateCache, $http, $q) {
      this.$templateCache = $templateCache;
      this.$http = $http;
      this.$q = $q;
      this.pending = [];
      this.pendingNames = {};
      this.sendPending = {};
    }

    /*
      * Converts a template path into a common template name
      * Eg: /deskpro/reports/load-view/Index/blank.html -> Index/blank.html
      *
      * @param {String} view
      * @return {String}
    */
    commonName(view) {
      view = view.replace(/^.*?\/reports\/load\-view\//g, '');
      return view;
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

      let qs = [];
      for (let t of Array.from(this.pending)) {
        qs.push(`views[]=${encodeURIComponent(t)}`);
      }
      qs.push(`v=${window.DP_BUILD_TIME}`);
      qs = qs.join('&');

      this.pending = [];
      this.sendPending = this.pendingNames;
      this.pendingNames = {};

      const preloadTpls = this.$http({
        method: 'GET',
        url: DP_BASE_REPORTS_URL + '/load-view/multi?' + qs
      });

      for (var k of Object.keys(this.sendPending || {})) {
        v = this.sendPending[k];
        this.sendPending[k] = preloadTpls;
      }

      preloadTpls.success( data => {
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
      const tpl = this.$templateCache.get(view);
      if (tpl) {
        return tpl;
      }
      return null;
    }


    /*
      * Loads a template source along with any others that are queued.
      *
      * @return {promise}
    */
    get(view) {
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

      this.load(view);
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
  }

  return Reports_Main_Service_TemplateManager;
});