(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
    var Admin_EmailStatus_Ctrl_SourceList;
    Admin_EmailStatus_Ctrl_SourceList = (function(_super) {
      __extends(Admin_EmailStatus_Ctrl_SourceList, _super);

      function Admin_EmailStatus_Ctrl_SourceList() {
        return Admin_EmailStatus_Ctrl_SourceList.__super__.constructor.apply(this, arguments);
      }

      Admin_EmailStatus_Ctrl_SourceList.CTRL_ID = 'Admin_EmailStatus_Ctrl_SourceList';

      Admin_EmailStatus_Ctrl_SourceList.CTRL_AS = 'ListCtrl';

      Admin_EmailStatus_Ctrl_SourceList.DEPS = [];

      Admin_EmailStatus_Ctrl_SourceList.prototype.init = function() {
        this.filter = {
          page: 1
        };
        this.results = [];
        this.num_results = 0;
        this.num_pages = 0;
        this.page_nums = [1];
        this.filter_date_mode = "none";
        this.page = 1;
        return this.$scope.$watch('ListCtrl.page', (function(_this) {
          return function(newVal, oldVal) {
            if (parseInt(newVal) === parseInt(oldVal)) {
              return;
            }
            if (isNaN(parseInt(newVal))) {
              return;
            }
            return _this.changePage();
          };
        })(this));
      };

      Admin_EmailStatus_Ctrl_SourceList.prototype.initialLoad = function() {
        return this.loadResults();
      };

      Admin_EmailStatus_Ctrl_SourceList.prototype.changePage = function() {
        if (this.filter.page === this.page) {
          return;
        }
        this.filter.page = this.page;
        return this.loadResults();
      };

      Admin_EmailStatus_Ctrl_SourceList.prototype.updateFilter = function() {
        this.page = 1;
        this.filter.page = this.page;
        this.filter.date_start = null;
        this.filter.date_end = null;
        if (this.filter_date_mode && this.filter_date_mode !== 'none') {
          if (this.filter_date1 && (this.filter_date_mode === 'between' || this.filter_date_mode === 'after')) {
            this.filter.date_start = moment(this.filter_date1).format("YYYY-MM-DD");
          }
          if (this.filter_date2 && (this.filter_date_mode === 'between' || this.filter_date_mode === 'before')) {
            this.filter.date_end = moment(this.filter_date2).format("YYYY-MM-DD");
          }
        }
        return this.loadResults();
      };

      Admin_EmailStatus_Ctrl_SourceList.prototype.loadResults = function() {
        var promise;
        this.startSpinner('loading_page');
        this.results = [];
        promise = this.Api.sendGet('/email_status/sources', {
          filter: this.filter
        }).success((function(_this) {
          return function(data) {
            var i, _i, _ref, _results;
            _this.stopSpinner('loading_page', true);
            _this.results = data.email_sources;
            _this.filter.page = data.page;
            _this.page = data.page;
            _this.num_pages = data.num_pages;
            _this.num_results = data.count;
            _this.page_nums = [];
            _results = [];
            for (i = _i = 0, _ref = _this.num_pages; 0 <= _ref ? _i < _ref : _i > _ref; i = 0 <= _ref ? ++_i : --_i) {
              _results.push(_this.page_nums.push(i + 1));
            }
            return _results;
          };
        })(this));
        return promise;
      };

      Admin_EmailStatus_Ctrl_SourceList.prototype.goPrevPage = function() {
        return this.page--;
      };

      Admin_EmailStatus_Ctrl_SourceList.prototype.goNextPage = function() {
        return this.page++;
      };

      return Admin_EmailStatus_Ctrl_SourceList;

    })(Admin_Ctrl_Base);
    return Admin_EmailStatus_Ctrl_SourceList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=SourceList.js.map
