(function() {
  define(['toastr'], function(toastr) {
    var Admin_Labels_Services_LabelManager;
    return Admin_Labels_Services_LabelManager = (function() {
      function Admin_Labels_Services_LabelManager(Api, $q) {
        this.Api = Api;
        this.$q = $q;
        this.label_types = {};
        this.label_types_promises = {};
      }

      Admin_Labels_Services_LabelManager.prototype.loadLabels = function(api_endpoint) {
        var d;
        if (this.label_types_promises[api_endpoint]) {
          return this.label_types_promises[api_endpoint];
        }
        d = this.$q.defer();
        this.Api.sendGet(api_endpoint).then((function(_this) {
          return function(result) {
            _this.label_types[api_endpoint] = result.data.labels;
            return d.resolve(_this.label_types[api_endpoint]);
          };
        })(this));
        this.label_types_promises[api_endpoint] = d.promise;
        return d.promise;
      };

      Admin_Labels_Services_LabelManager.prototype.addLabel = function(api_endpoint, label) {
        return this.loadLabels(api_endpoint).then((function(_this) {
          return function() {
            return _this.label_types[api_endpoint].push({
              label: label,
              count: 0
            });
          };
        })(this));
      };

      Admin_Labels_Services_LabelManager.prototype.renameLabel = function(api_endpoint, old_label, new_label) {
        return this.loadLabels(api_endpoint).then((function(_this) {
          return function() {
            var dupe, key, l, oldIdx, _i, _len, _ref;
            dupe = false;
            oldIdx = null;
            _ref = _this.label_types[api_endpoint];
            for (key = _i = 0, _len = _ref.length; _i < _len; key = ++_i) {
              l = _ref[key];
              if (l.label === new_label) {
                dupe = true;
              }
              if (l.label === old_label) {
                oldIdx = key;
              }
            }
            if (dupe) {
              return _this.label_types[api_endpoint].splice(oldIdx, 1);
            } else if (oldIdx) {
              return _this.label_types[api_endpoint][oldIdx].label = new_label;
            }
          };
        })(this));
      };

      Admin_Labels_Services_LabelManager.prototype.removeLabel = function(api_endpoint, label) {
        return this.loadLabels(api_endpoint).then((function(_this) {
          return function(labels) {
            var idx, k, l, _i, _len;
            idx = null;
            for (k = _i = 0, _len = labels.length; _i < _len; k = ++_i) {
              l = labels[k];
              if (l.label === label) {
                idx = k;
                break;
              }
            }
            if (idx !== null) {
              return labels.splice(idx, 1);
            }
          };
        })(this));
      };

      return Admin_Labels_Services_LabelManager;

    })();
  });

}).call(this);

//# sourceMappingURL=LabelManager.js.map
