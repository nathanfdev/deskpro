(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base'], function(ReportsBaseCtrl) {
    var Reports_Billing_Ctrl_View;
    Reports_Billing_Ctrl_View = (function(_super) {
      __extends(Reports_Billing_Ctrl_View, _super);

      function Reports_Billing_Ctrl_View() {
        return Reports_Billing_Ctrl_View.__super__.constructor.apply(this, arguments);
      }

      Reports_Billing_Ctrl_View.CTRL_ID = 'Reports_Billing_Ctrl_View';

      Reports_Billing_Ctrl_View.CTRL_AS = 'Ctrl';

      Reports_Billing_Ctrl_View.DEPS = ['$stateParams', '$sce', 'Api'];

      Reports_Billing_Ctrl_View.prototype.init = function() {
        return this.rendered_result = null;
      };


      /*
       	 *
       */

      Reports_Billing_Ctrl_View.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet('/reports/billing/' + this.$stateParams.id, {
          params: this.$stateParams.params
        }).then((function(_this) {
          return function(res) {
            var data;
            data = res.data;
            return _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result);
          };
        })(this));
        return promise;
      };

      return Reports_Billing_Ctrl_View;

    })(ReportsBaseCtrl);
    return Reports_Billing_Ctrl_View.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=View.js.map
