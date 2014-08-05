(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'angular'], function(Admin_Main_DataService_BaseListEdit, angular) {
    var Admin_RoundRobin_DataService_RoundRobin;
    return Admin_RoundRobin_DataService_RoundRobin = (function(_super) {
      var settings;

      __extends(Admin_RoundRobin_DataService_RoundRobin, _super);

      function Admin_RoundRobin_DataService_RoundRobin() {
        return Admin_RoundRobin_DataService_RoundRobin.__super__.constructor.apply(this, arguments);
      }

      Admin_RoundRobin_DataService_RoundRobin.$inject = ['Api', '$q'];

      settings = {};

      Admin_RoundRobin_DataService_RoundRobin.prototype.url = function() {
        return '/round_robin';
      };

      Admin_RoundRobin_DataService_RoundRobin.prototype.getSettings = function(reload) {
        var def;
        def = this.$q.defer();
        if ((settings.enabled != null) && (reload == null)) {
          def.resolve(settings);
        } else {
          this.Api.sendGet(this.url() + '/settings').then((function(_this) {
            return function(data) {
              angular.copy(data.data, settings);
              return def.resolve(settings);
            };
          })(this));
        }
        return def.promise;
      };

      Admin_RoundRobin_DataService_RoundRobin.prototype.saveSettings = function() {
        return this.Api.sendPutJson(this.url() + '/settings', settings);
      };

      Admin_RoundRobin_DataService_RoundRobin.prototype.checkTriggers = function(id) {
        var def, url;
        url = this.url() + '/triggers';
        if (id != null) {
          url += "/" + id;
        }
        def = this.$q.defer();
        this.Api.sendGet(url).then((function(_this) {
          return function(data) {
            return def.resolve(data.data);
          };
        })(this));
        return def.promise;
      };

      return Admin_RoundRobin_DataService_RoundRobin;

    })(Admin_Main_DataService_BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=RoundRobin.js.map
