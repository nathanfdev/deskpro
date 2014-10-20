(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
    var Admin_EmailStatus_Ctrl_SendmailList;
    Admin_EmailStatus_Ctrl_SendmailList = (function(_super) {
      __extends(Admin_EmailStatus_Ctrl_SendmailList, _super);

      function Admin_EmailStatus_Ctrl_SendmailList() {
        return Admin_EmailStatus_Ctrl_SendmailList.__super__.constructor.apply(this, arguments);
      }

      Admin_EmailStatus_Ctrl_SendmailList.CTRL_ID = 'Admin_EmailStatus_Ctrl_SendmailList';

      Admin_EmailStatus_Ctrl_SendmailList.CTRL_AS = 'ListCtrl';

      Admin_EmailStatus_Ctrl_SendmailList.DEPS = ['DpDateService'];

      Admin_EmailStatus_Ctrl_SendmailList.prototype.init = function() {
        this.filter = {
          page: 1
        };
        this.results = [];
        this.num_results = 0;
        this.num_pages = 0;
        this.page_nums = [1];
        this.filter_date_mode = "none";
        this.page = 1;
        this.massActionsOp = "resend";
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

      Admin_EmailStatus_Ctrl_SendmailList.prototype.initialLoad = function() {
        return this.loadResults();
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.changePage = function() {
        if (this.filter.page === this.page) {
          return;
        }
        this.filter.page = this.page;
        return this.loadResults();
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.updateFilter = function() {
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

      Admin_EmailStatus_Ctrl_SendmailList.prototype.loadResults = function(fallbackPrevPage) {
        var promise;
        this.startSpinner('loading_page');
        this.results = [];
        promise = this.Api.sendGet('/email_status/sendmail', {
          filter: this.filter
        }).success((function(_this) {
          return function(data) {
            var i, _i, _ref;
            _this.stopSpinner('loading_page', true);
            _this.results = data.sendmail_queue;
            _this.page = data.page;
            _this.num_pages = data.num_pages;
            _this.num_results = data.count;
            _this.massActions = {};
            _this.massActionsAll = false;
            _this.massActionsLoading = false;
            _this.page_nums = [];
            for (i = _i = 0, _ref = _this.num_pages; 0 <= _ref ? _i < _ref : _i > _ref; i = 0 <= _ref ? ++_i : --_i) {
              _this.page_nums.push(i + 1);
            }
            _this.results.map(function(res) {
              res.date_created = _this.DpDateService.local(res.date_created);
              if (res.date_sent) {
                res.date_sent = _this.DpDateService.local(res.date_sent);
              }
              if (res.date_next_attempt) {
                return res.date_next_attempt = _this.DpDateService.local(res.date_next_attempt);
              }
            });
            if (fallbackPrevPage && !_this.results.length && data.page > 1) {
              _this.filter.page = data.page - 1;
              return _this.loadResults();
            }
          };
        })(this));
        return promise;
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.toggleMassActions = function() {
        var r, _i, _len, _ref, _results;
        this.massActions = {};
        if (this.massActionsAll) {
          _ref = this.results;
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            r = _ref[_i];
            _results.push(this.massActions[r.id] = true);
          }
          return _results;
        }
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.hasAnyMassActions = function() {
        var r, _i, _len, _ref;
        _ref = this.results;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          r = _ref[_i];
          if (this.massActions[r.id]) {
            return true;
          }
        }
        return false;
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.performMassActions = function() {
        var ids, r, url, _i, _len, _ref;
        url = "/email_status/sendmail/mass-actions/" + this.massActionsOp;
        this.massActionsLoading = true;
        ids = [];
        _ref = this.results;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          r = _ref[_i];
          if (this.massActions[r.id]) {
            ids.push(r.id);
          }
        }
        return this.Api.sendPostJson(url, {
          ids: ids
        }).then((function(_this) {
          return function() {
            _this.Growl.success(_this.getRegisteredMessage("" + _this.massActionsOp + "_done"));
            return _this.loadResults(true);
          };
        })(this));
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.goPrevPage = function() {
        return this.page--;
      };

      Admin_EmailStatus_Ctrl_SendmailList.prototype.goNextPage = function() {
        return this.page++;
      };

      return Admin_EmailStatus_Ctrl_SendmailList;

    })(Admin_Ctrl_Base);
    return Admin_EmailStatus_Ctrl_SendmailList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=SendmailList.js.map
