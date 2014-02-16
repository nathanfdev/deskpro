(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'moment'], function(ReportsBaseCtrl, moment) {
    var Reports_TicketSatisfaction_Ctrl_List, _ref;
    return Reports_TicketSatisfaction_Ctrl_List = (function(_super) {
      __extends(Reports_TicketSatisfaction_Ctrl_List, _super);

      function Reports_TicketSatisfaction_Ctrl_List() {
        _ref = Reports_TicketSatisfaction_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Reports_TicketSatisfaction_Ctrl_List.CTRL_ID = 'Reports_TicketSatisfaction_Ctrl_List';

      Reports_TicketSatisfaction_Ctrl_List.CTRL_AS = 'ListCtrl';

      Reports_TicketSatisfaction_Ctrl_List.DEPS = ['Api', '$sce'];

      /*
      		# Initializing..
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.init = function() {
        this.html = '';
        this.page_nums = [1];
        this.num_pages = 0;
        return this.page = 1;
      };

      /*
      		# Just doing all the necessary AJAX calls here
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.initialLoad = function() {
        return this.loadResults();
      };

      /*
      		# This method updates current parameters that are used for sending request to API
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.updateFilter = function() {
        return this.loadResults();
      };

      /*
      		# Loading the results of sending request to API
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.loadResults = function() {
        var promise,
          _this = this;
        this.startSpinner('loading_list_results');
        promise = this.Api.sendGet("/reports/ticket-satisfaction/" + this.page).then(function(res) {
          var i, _i, _ref1;
          _this.html = _this.$sce.trustAsHtml(res.data.html);
          _this.page = res.data.page;
          _this.num_pages = res.data.num_pages;
          _this.page_nums = [];
          for (i = _i = 0, _ref1 = _this.num_pages; 0 <= _ref1 ? _i < _ref1 : _i > _ref1; i = 0 <= _ref1 ? ++_i : --_i) {
            _this.page_nums.push(i + 1);
          }
          return _this.stopSpinner('loading_list_results', true);
        });
        return promise;
      };

      /*
      		# This is executed after we changed the current page
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.changePage = function() {
        return this.loadResults();
      };

      /*
      		#
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.goPrevPage = function() {
        this.page--;
        return this.changePage();
      };

      /*
      		#
      */


      Reports_TicketSatisfaction_Ctrl_List.prototype.goNextPage = function() {
        this.page++;
        return this.changePage();
      };

      Reports_TicketSatisfaction_Ctrl_List.EXPORT_CTRL();

      return Reports_TicketSatisfaction_Ctrl_List;

    })(ReportsBaseCtrl);
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/