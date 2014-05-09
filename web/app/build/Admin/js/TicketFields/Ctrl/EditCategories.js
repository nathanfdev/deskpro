(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
    var Admin_TicketFields_Ctrl_EditCategories;
    Admin_TicketFields_Ctrl_EditCategories = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_EditCategories, _super);

      function Admin_TicketFields_Ctrl_EditCategories() {
        return Admin_TicketFields_Ctrl_EditCategories.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFields_Ctrl_EditCategories.CTRL_ID = 'Admin_TicketFields_Ctrl_EditCategories';

      Admin_TicketFields_Ctrl_EditCategories.CTRL_AS = 'TicketCats';

      Admin_TicketFields_Ctrl_EditCategories.DEPS = [];

      Admin_TicketFields_Ctrl_EditCategories.prototype.init = function() {
        this.cats = [];
        this.default_id = 0;
        this.agent_required = false;
        this.user_required = false;
        this.cat_parent_list = [];
        this.$scope.$watchCollection('TicketCats.cats', (function(_this) {
          return function() {
            return _this.updateCatParentList();
          };
        })(this), true);
      };

      Admin_TicketFields_Ctrl_EditCategories.prototype.updateCatParentList = function() {
        var cat, flat, valid_ids, _i, _len;
        this.cat_parent_list = [];
        flat = Arrays.analyzeFlatCatStructure(this.cats);
        valid_ids = [];
        for (_i = 0, _len = flat.length; _i < _len; _i++) {
          cat = flat[_i];
          if (!cat.child_ids.length) {
            valid_ids.push(cat.id);
            this.cat_parent_list.push({
              id: cat.id,
              title: cat.full_title
            });
          }
        }
        if (valid_ids.indexOf(this.default_id) === -1) {
          return this.default_id = 0;
        }
      };

      Admin_TicketFields_Ctrl_EditCategories.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'info': '/ticket_cats'
        }).then((function(_this) {
          return function(res) {
            _this.cats = res.data.info.categories;
            _this.default_id = res.data.info.default_id;
            _this.agent_required = res.data.info.agent_required;
            _this.user_required = res.data.info.user_required;
            return _this.updateCatParentList();
          };
        })(this));
        return data_promise;
      };

      Admin_TicketFields_Ctrl_EditCategories.prototype.save = function() {
        var postData, promise;
        postData = {
          categories: this.cats,
          default_id: this.default_id,
          user_required: this.user_required,
          agent_required: this.agent_required
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/ticket_cats', postData).success((function(_this) {
          return function() {
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_TicketFields_Ctrl_EditCategories;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_EditCategories.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditCategories.js.map
