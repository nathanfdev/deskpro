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
        this.colors = {};
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
                _this.colors[label] = def.color;
              }
              return d.resolve(_this.definitions);
            });
            return loadPromise = d.promise;
          };
        })(this);
        updateColorForLabel = (function(_this) {
          return function(color, label) {
            var label_type, _label, _results;
            label = label.toLowerCase();
            _results = [];
            for (label_type in _this.definitions) {
              _results.push((function() {
                var _results1;
                _results1 = [];
                for (_label in this.definitions[label_type]) {
                  if (_label === label) {
                    this.definitions[label_type][_label].color = color;
                    _results1.push(this.colors[label] = color);
                  } else {
                    _results1.push(void 0);
                  }
                }
                return _results1;
              }).call(_this));
            }
            return _results;
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
            var _ref;
            return d.resolve((_ref = _this.definitions[label_type]) != null ? _ref[label] : void 0);
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
          delete this.colors[_old.label.toLowerCase()];
        }
        this.definitions[_new.label_type] = this.definitions[_new.label_type] || {};
        this.definitions[_new.label_type][label] = _new;
        return updateColorForLabel(_new.color, label);
      };

      DeskPRO_Service_LabelDefinition.prototype.remove = function(def) {
        var _ref;
        if (!def.label || !def.label_type) {
          return;
        }
        if (((_ref = this.definitions[def.label_type]) != null ? _ref[def.label.toLowerCase()] : void 0) != null) {
          delete this.definitions[def.label_type][def.label.toLowerCase()];
          delete this.colors[def.label.toLowerCase()];
        }
        return updateColorForLabel(def.color, def.label);
      };

      DeskPRO_Service_LabelDefinition.prototype.getColor = function(label) {
        var d;
        d = this.$q.defer();
        label = (label || '').toLowerCase();
        loadDefinitions().then((function(_this) {
          return function() {
            return d.resolve(_this.colors[label] || '');
          };
        })(this));
        return d.promise;
      };

      return DeskPRO_Service_LabelDefinition;

    })();
  });

}).call(this);

//# sourceMappingURL=LabelDefinition.js.map
