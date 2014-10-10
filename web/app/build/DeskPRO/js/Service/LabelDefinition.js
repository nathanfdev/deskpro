(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

  define(['angular'], function(angular) {
    var DeskPRO_Service_LabelDefinition;
    return DeskPRO_Service_LabelDefinition = (function() {
      var loadDefinitions, updateColorForLabel;

      loadDefinitions = null;

      updateColorForLabel = null;

      function DeskPRO_Service_LabelDefinition($q, definitionsPromise) {
        var loadPromise;
        this.$q = $q;
        this.all = __bind(this.all, this);
        loadPromise = null;
        this.definitions = {
          tickets: {},
          people: {},
          organizations: {},
          news: {},
          kb: {},
          feedback: {},
          downloads: {},
          chat: {}
        };
        loadDefinitions = (function(_this) {
          return function() {
            var d;
            if (loadPromise) {
              return loadPromise;
            }
            d = _this.$q.defer();
            definitionsPromise.then(function(data) {
              var def, label, _i, _len, _ref;
              _ref = data.data;
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                def = _ref[_i];
                if ((def.label == null) || (def.label_type == null)) {
                  continue;
                }
                label = def.label.toLowerCase();
                _this.definitions[def.label_type] = _this.definitions[def.label_type] || {};
                _this.definitions[def.label_type][label] = angular.copy(def);
              }
              return d.resolve(_this.definitions);
            });
            return loadPromise = d.promise;
          };
        })(this);
      }

      DeskPRO_Service_LabelDefinition.prototype.all = function(label_type) {
        var d;
        d = this.$q.defer();
        loadDefinitions().then((function(_this) {
          return function() {
            return d.resolve(_this.definitions[label_type]);
          };
        })(this));
        return d.promise;
      };

      DeskPRO_Service_LabelDefinition.prototype.get = function(label_type, label) {
        var d;
        d = this.$q.defer();
        label = (label || '').toLowerCase();
        loadDefinitions().then((function(_this) {
          return function() {
            var val;
            if (_this.definitions[label_type]) {
              val = _this.definitions[label_type][label];
            } else {
              val = null;
            }
            return d.resolve(val);
          };
        })(this));
        return d.promise;
      };

      DeskPRO_Service_LabelDefinition.prototype.update = function(_old, _new) {
        var label;
        if (!_new.label || !_new.label_type) {
          return;
        }
        label = _new.label.toLowerCase();
        if (_old) {
          delete this.definitions[_old.label_type][_old.label.toLowerCase()];
        }
        this.definitions[_new.label_type] = this.definitions[_new.label_type] || {};
        return this.definitions[_new.label_type][label] = _new;
      };

      DeskPRO_Service_LabelDefinition.prototype.remove = function(def) {
        var _ref;
        if (!def.label || !def.label_type) {
          return;
        }
        if (((_ref = this.definitions[def.label_type]) != null ? _ref[def.label.toLowerCase()] : void 0) != null) {
          return delete this.definitions[def.label_type][def.label.toLowerCase()];
        }
      };

      return DeskPRO_Service_LabelDefinition;

    })();
  });

}).call(this);

//# sourceMappingURL=LabelDefinition.js.map
