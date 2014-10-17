(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'moment'], function(ReportsBaseCtrl, moment) {
    var Reports_TicketSatisfaction_Ctrl_TicketSatisfaction;
    return Reports_TicketSatisfaction_Ctrl_TicketSatisfaction = (function(_super) {
      __extends(Reports_TicketSatisfaction_Ctrl_TicketSatisfaction, _super);

      function Reports_TicketSatisfaction_Ctrl_TicketSatisfaction() {
        return Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.__super__.constructor.apply(this, arguments);
      }

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.CTRL_ID = 'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction';

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.CTRL_AS = 'Ctrl';

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.DEPS = ['Api', '$sce'];

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.init = function() {
        this.feed_html = '';
        this.summary_html = '';
        this.page_nums = [1];
        this.num_pages = 0;
        this.page = 1;
        this.$scope.view_date = moment(this.date).format("YYYY-MM");
        return this.$scope.mode = 'feed';
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.initialLoad = function() {
        return this.loadFeedResults();
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.switchToFeed = function() {
        this.$scope.mode = 'feed';
        return this.loadFeedResults();
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.switchToSummary = function() {
        this.$scope.mode = 'summary';
        return this.loadSummaryResults();
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.loadFeedResults = function() {
        var promise;
        this.startSpinner('loading_feed_results');
        promise = this.Api.sendGet("/reports/ticket-satisfaction/" + this.page).then((function(_this) {
          return function(res) {
            var i, _i, _ref;
            _this.feed_html = _this.$sce.trustAsHtml(res.data.html);
            _this.page = res.data.page || 1;
            _this.num_pages = res.data.num_pages || 1;
            _this.page_nums = [];
            for (i = _i = 0, _ref = _this.num_pages; 0 <= _ref ? _i < _ref : _i > _ref; i = 0 <= _ref ? ++_i : --_i) {
              _this.page_nums.push(i + 1);
            }
            return _this.stopSpinner('loading_feed_results', true);
          };
        })(this));
        return promise;
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.loadSummaryResults = function() {
        var promise;
        this.startSpinner('loading_summary_results');
        promise = this.Api.sendGet("/reports/ticket-satisfaction/summary/" + this.$scope.view_date).then((function(_this) {
          return function(res) {
            _this.summary_html = _this.$sce.trustAsHtml(res.data.html);
            return _this.stopSpinner('loading_summary_results', true);
          };
        })(this));
        return promise;
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.changePage = function() {
        return this.loadFeedResults();
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.goPrevPage = function() {
        this.page--;
        return this.changePage();
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.prototype.goNextPage = function() {
        this.page++;
        return this.changePage();
      };

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.EXPORT_CTRL();

      return Reports_TicketSatisfaction_Ctrl_TicketSatisfaction;

    })(ReportsBaseCtrl);
  });

}).call(this);

//# sourceMappingURL=TicketSatisfaction.js.map
