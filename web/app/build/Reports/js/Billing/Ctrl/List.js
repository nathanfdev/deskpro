(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(ReportsBaseCtrl, Util) {
    var Reports_Billing_Ctrl_List;
    Reports_Billing_Ctrl_List = (function(_super) {
      __extends(Reports_Billing_Ctrl_List, _super);

      function Reports_Billing_Ctrl_List() {
        return Reports_Billing_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Reports_Billing_Ctrl_List.CTRL_ID = 'Reports_Billing_Ctrl_List';

      Reports_Billing_Ctrl_List.CTRL_AS = 'ListCtrl';

      Reports_Billing_Ctrl_List.DEPS = ['Api', '$timeout'];

      Reports_Billing_Ctrl_List.prototype.init = function() {
        return this.reports_list = [
          {
            id: 'list-charges-date',
            title: 'List of charges <1:date group, default: today>'
          }, {
            id: 'total-charges-per-day-date',
            title: 'Total charges per day <1:date group, default: this_month>'
          }, {
            id: 'total-amount-charges-per-day-date',
            title: 'Total amount charges per day <1:date group, default: this_month>'
          }, {
            id: 'total-time-charges-per-day-date',
            title: 'Total time charges per day <1:date group, default: this_month>'
          }, {
            id: 'total-charges-person-date',
            title: 'Total charges per person <1:date group, default: this_month>'
          }, {
            id: 'total-amount-charges-person-date',
            title: 'Total amount charges per person <1:date group, default: this_month>'
          }, {
            id: 'total-time-charges-person-date',
            title: 'Total time charges per person <1:date group, default: this_month>'
          }, {
            id: 'list-charges-person-date',
            title: 'List of charges per person <1:date group, default: this_month>'
          }, {
            id: 'total-charges-organization-date',
            title: 'Total charges per organization <1:date group, default: this_month>'
          }, {
            id: 'total-amount-charges-organization-date',
            title: 'Total amount charges per organization <1:date group, default: this_month>'
          }, {
            id: 'total-time-charges-organization-date',
            title: 'Total time charges per organization <1:date group, default: this_month>'
          }, {
            id: 'list-charges-organization-date',
            title: 'List of charges per organization <1:date group, default: this_month>'
          }, {
            id: 'total-charges-agent-date',
            title: 'Total charges per agent <1:date group, default: this_month>'
          }, {
            id: 'total-amount-charges-agent-date',
            title: 'Total amount charges per agent <1:date group, default: this_month>'
          }, {
            id: 'total-time-charges-agent-date',
            title: 'Total time charges per agent <1:date group, default: this_month>'
          }, {
            id: 'list-charges-agent-date',
            title: 'List of charges per agent <1:date group, default: this_month>'
          }
        ];
      };


      /*
      		 * Loads 2 lists - first with custom reports, second with built-in reports
       */

      Reports_Billing_Ctrl_List.prototype.initialLoad = function() {
        var d, group_params_promise;
        d = this.$q.defer();
        group_params_promise = this.Api.sendGet('/reports/builder/group-params').then((function(_this) {
          return function(data) {
            _this.group_params = data.data;
            return _this.$timeout(function() {
              return d.resolve();
            }, 150);
          };
        })(this));
        return d.promise;
      };

      return Reports_Billing_Ctrl_List;

    })(ReportsBaseCtrl);
    return Reports_Billing_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
