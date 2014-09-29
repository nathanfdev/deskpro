(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var Admin_Main_Service_TemplateManager;
    Admin_Main_Service_TemplateManager = (function() {
      function Admin_Main_Service_TemplateManager($templateCache, $http, $q) {
        this.$templateCache = $templateCache;
        this.$http = $http;
        this.$q = $q;
        this.pending = [];
        this.pendingNames = {};
        this.sendPending = {};
      }


      /*
        	 * Converts a template path into a common template name
        	 * Eg: /deskpro/admin/load-view/Index/blank.html -> Index/blank.html
        	 *
        	 * @param {String} view
        	 * @return {String}
       */

      Admin_Main_Service_TemplateManager.prototype.commonName = function(view) {
        view = view.replace(/^.*?\/admin\/load\-view\//g, '');
        return view;
      };


      /*
      		 * Mark a view to be loaded next time we are loading templates
        	 *
        	 * @param {String} view
       */

      Admin_Main_Service_TemplateManager.prototype.load = function(view) {
        view = this.commonName(view);
        if (!this.$templateCache.get(view) && !this.pendingNames[view] && !this.sendPending[view]) {
          this.pending.push(view);
          return this.pendingNames[view] = true;
        }
      };


      /*
        	 * Sets a template in the template cache
        	 *
        	 * @param {String} view
        	 * @param {String} source
       */

      Admin_Main_Service_TemplateManager.prototype.setTemplate = function(view, source) {
        view = this.commonName(view);
        return this.$templateCache.put(view, source);
      };


      /*
        	 * Removes a template from the cache
        	 *
        	 * @param {String} view
       */

      Admin_Main_Service_TemplateManager.prototype.removeTemplate = function(view) {
        return this.$templateCache.remove(this.commonName(view));
      };


      /*
        	 * Execute the pending loads by ending the http request.
        	 *
        	 * @return {promise}
       */

      Admin_Main_Service_TemplateManager.prototype.loadPending = function() {
        var d, k, preloadTpls, qs, t, v, _i, _len, _ref, _ref1;
        if (!this.pending.length) {
          d = this.$q.defer();
          d.resolve();
          return d.promise;
        }
        qs = [];
        _ref = this.pending;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          t = _ref[_i];
          qs.push('views[]=' + encodeURIComponent(t));
        }
        qs.push('v=' + window.DP_BUILD_TIME);
        qs = qs.join('&');
        this.pending = [];
        this.sendPending = this.pendingNames;
        this.pendingNames = {};
        preloadTpls = this.$http({
          method: 'GET',
          url: DP_BASE_ADMIN_URL + '/load-view/multi?' + qs
        });
        _ref1 = this.sendPending;
        for (k in _ref1) {
          if (!__hasProp.call(_ref1, k)) continue;
          v = _ref1[k];
          this.sendPending[k] = preloadTpls;
        }
        preloadTpls.success((function(_this) {
          return function(data) {
            var tpl, _j, _len1, _ref2, _results;
            for (_j = 0, _len1 = data.length; _j < _len1; _j++) {
              tpl = data[_j];
              _this.$templateCache.put(tpl.id, tpl.source);
            }
            _ref2 = _this.sendPending;
            _results = [];
            for (k in _ref2) {
              if (!__hasProp.call(_ref2, k)) continue;
              v = _ref2[k];
              if (v === preloadTpls) {
                _this.sendPending[k] = null;
                _results.push(delete _this.sendPending[k]);
              } else {
                _results.push(void 0);
              }
            }
            return _results;
          };
        })(this));
        return preloadTpls;
      };


      /*
        	 * Gets the template source if it is already loaded, or null if it isnt
        	 *
        	 * @return {String|null}
       */

      Admin_Main_Service_TemplateManager.prototype.getNow = function(view) {
        var tpl;
        view = this.commonName(view);
        tpl = this.$templateCache.get(view);
        if (tpl) {
          return tpl;
        }
        return null;
      };


      /*
        	 * Loads a template source along with any others that are queued.
        	 *
        	 * @return {promise}
       */

      Admin_Main_Service_TemplateManager.prototype.get = function(view) {
        var d, defer, exist, promise;
        view = this.commonName(view);
        exist = this.$templateCache.get(view);
        if (exist || exist === "") {
          d = this.$q.defer();
          d.resolve(this.$templateCache.get(view));
          return d.promise;
        }
        if (this.sendPending[view]) {
          d = this.$q.defer();
          this.sendPending[view].then((function(_this) {
            return function() {
              return d.resolve(_this.$templateCache.get(view));
            };
          })(this));
          return d.promise;
        }
        this.load(view);
        promise = this.loadPending();
        defer = this.$q.defer();
        promise.then((function(_this) {
          return function() {
            var tpl;
            tpl = _this.$templateCache.get(view);
            if (tpl || tpl === "") {
              return defer.resolve(tpl);
            } else {
              console.log("Failed to load %s", view);
              return defer.reject("failed");
            }
          };
        })(this));
        return defer.promise;
      };

      return Admin_Main_Service_TemplateManager;

    })();
    return Admin_Main_Service_TemplateManager;
  });

}).call(this);

//# sourceMappingURL=TemplateManager.js.map
