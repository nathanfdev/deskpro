(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseModel'], function(BaseModel) {
    var LabelSettings;
    return LabelSettings = (function(_super) {
      __extends(LabelSettings, _super);

      function LabelSettings() {
        return LabelSettings.__super__.constructor.apply(this, arguments);
      }

      LabelSettings.prototype.init = function() {
        this.loaded = {};
        return this.model = {};
      };

      LabelSettings.prototype.url = function(type) {
        if (!type) {
          throw "Type must be defined";
        }
        return "/labels/" + type + "/settings";
      };

      LabelSettings.prototype.get = function(type, reload) {
        var deferred;
        if (!type) {
          throw "Type must be defined";
        }
        deferred = this.$q.defer();
        if ((this.loaded[type] != null) && (reload == null)) {
          deferred.resolve(this.model[type]);
          return deferred.promise;
        }
        this._doGet(type).then((function(_this) {
          return function(data) {
            if (_this.model[type] == null) {
              _this.model[type] = {};
            }
            _this.loaded[type] = true;
            return deferred.resolve(angular.copy(data, _this.model[type]));
          };
        })(this), (function(_this) {
          return function(res) {
            return deferred.reject(res);
          };
        })(this));
        return deferred.promise;
      };

      LabelSettings.prototype._doGet = function(type) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet(this.url(type)).success((function(_this) {
          return function(data) {
            return deferred.resolve(_this.resolveResponse(data));
          };
        })(this)).error(function(data, status, headers, config) {
          return deferred.reject(data);
        });
        return deferred.promise;
      };

      LabelSettings.prototype.set = function(type) {
        var deferred;
        if (!type) {
          throw "Type must be defined";
        }
        if (this.loaded[type] == null) {
          this.get(type);
        }
        deferred = this.$q.defer();
        this._doSet(type).then((function(_this) {
          return function(data) {
            return deferred.resolve(angular.copy(data, _this.model[type]));
          };
        })(this), (function(_this) {
          return function(res) {
            return deferred.reject(res);
          };
        })(this));
        return deferred.promise;
      };

      LabelSettings.prototype._doSet = function(type) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendPutJson(this.url(type), this.model[type]).success((function(_this) {
          return function(data) {
            return deferred.resolve(_this.resolveResponse(data));
          };
        })(this)).error((function(_this) {
          return function(data, status, headers, config) {
            return deferred.reject({
              info: data.error_message,
              status: status
            });
          };
        })(this));
        return deferred.promise;
      };

      return LabelSettings;

    })(BaseModel);
  });

}).call(this);

//# sourceMappingURL=Settings.js.map
