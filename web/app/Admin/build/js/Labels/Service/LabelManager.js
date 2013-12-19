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
        var d,
          _this = this;
        if (this.label_types_promises[api_endpoint]) {
          return this.label_types_promises[api_endpoint];
        }
        d = this.$q.defer();
        this.Api.sendGet(api_endpoint).then(function(result) {
          _this.label_types[api_endpoint] = result.data.labels;
          return d.resolve(_this.label_types[api_endpoint]);
        });
        this.label_types_promises[api_endpoint] = d.promise;
        return d.promise;
      };

      Admin_Labels_Services_LabelManager.prototype.addLabel = function(api_endpoint, label) {
        var _this = this;
        return this.loadLabels(api_endpoint).then(function() {
          return _this.label_types[api_endpoint].push({
            label: label,
            count: 0
          });
        });
      };

      Admin_Labels_Services_LabelManager.prototype.renameLabel = function(api_endpoint, old_label, new_label) {
        var _this = this;
        return this.loadLabels(api_endpoint).then(function() {
          var l, _i, _len, _ref, _results;
          _ref = _this.label_types[api_endpoint];
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            l = _ref[_i];
            if (l.label === old_label) {
              l.label = new_label;
              break;
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        });
      };

      Admin_Labels_Services_LabelManager.prototype.removeLabel = function(api_endpoint, label) {
        var _this = this;
        return this.loadLabels(api_endpoint).then(function(labels) {
          var idx, k, l, _i, _len;
          idx = null;
          for (k = _i = 0, _len = labels.length; _i < _len; k = ++_i) {
            l = labels[k];
            console.log(l);
            if (l.label === label) {
              idx = k;
              break;
            }
          }
          if (idx !== null) {
            return labels.splice(idx, 1);
          }
        });
      };

      return Admin_Labels_Services_LabelManager;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=LabelManager.js.map
*/