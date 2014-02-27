(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Agents_Ctrl_Logs;
    Admin_Agents_Ctrl_Logs = (function(_super) {
      __extends(Admin_Agents_Ctrl_Logs, _super);

      function Admin_Agents_Ctrl_Logs() {
        return Admin_Agents_Ctrl_Logs.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_Logs.CTRL_ID = 'Admin_Agents_Ctrl_Logs';

      Admin_Agents_Ctrl_Logs.CTRL_AS = 'LogsCtrl';

      Admin_Agents_Ctrl_Logs.DEPS = [];

      Admin_Agents_Ctrl_Logs.prototype.init = function() {
        this.login_logs = null;
        this.page = 1;
        this.num_pages = 0;
        this.page_nums = [1];
        return this.initializeScopeWatching();
      };

      Admin_Agents_Ctrl_Logs.prototype.initialLoad = function() {
        return this.loadResults();
      };


      /*
       	 *
       */

      Admin_Agents_Ctrl_Logs.prototype.loadResults = function() {
        var data_promise;
        this.startSpinner('paginating_login_logs');
        data_promise = this.Api.sendGet('/login_logs', {
          page: this.page
        }).then((function(_this) {
          return function(res) {
            var i, _i, _ref;
            _this.login_logs = res.data.login_logs;
            _this.num_pages = res.data.login_logs.num_pages;
            _this.page_nums = [];
            for (i = _i = 0, _ref = _this.num_pages; 0 <= _ref ? _i < _ref : _i > _ref; i = 0 <= _ref ? ++_i : --_i) {
              _this.page_nums.push(i + 1);
            }
            return _this.stopSpinner('paginating_login_logs', true);
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
       	 *
       */

      Admin_Agents_Ctrl_Logs.prototype.updateFilter = function() {
        return this.loadResults();
      };


      /*
      		 *	Here we watching scope 'page' variable in order to load new page of results
       */

      Admin_Agents_Ctrl_Logs.prototype.initializeScopeWatching = function() {
        return this.$scope.$watch('LogsCtrl.page', (function(_this) {
          return function(newVal, oldVal) {
            if (parseInt(newVal) === parseInt(oldVal)) {
              return void 0;
            }
            if (isNaN(parseInt(newVal))) {
              return void 0;
            }
            return _this.changePageCallback();
          };
        })(this));
      };


      /*
      		 * This is executed after we changed the current page
       */

      Admin_Agents_Ctrl_Logs.prototype.changePageCallback = function() {
        return this.loadResults();
      };


      /*
       	 *
       */

      Admin_Agents_Ctrl_Logs.prototype.goPrevPage = function() {
        return this.page--;
      };


      /*
      		 *
       */

      Admin_Agents_Ctrl_Logs.prototype.goNextPage = function() {
        return this.page++;
      };

      return Admin_Agents_Ctrl_Logs;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_Logs.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Logs.js.map
