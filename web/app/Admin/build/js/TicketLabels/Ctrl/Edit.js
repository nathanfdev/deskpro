(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketLabels_Ctrl_Edit, _ref;
    Admin_TicketLabels_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketLabels_Ctrl_Edit, _super);

      function Admin_TicketLabels_Ctrl_Edit() {
        _ref = Admin_TicketLabels_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketLabels_Ctrl_Edit.CTRL_ID = 'Admin_TicketLabels_Ctrl_Edit';

      Admin_TicketLabels_Ctrl_Edit.CTRL_AS = 'TicketLabelsEdit';

      Admin_TicketLabels_Ctrl_Edit.DEPS = ['$scope', 'Growl', 'TicketLabelsData', 'Api', '$stateParams'];

      Admin_TicketLabels_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketLabels_Ctrl_Edit.prototype.init = function() {
        this.label_object = {
          label: '',
          count: 0
        };
        this.$scope.saving_label = false;
        this.form = {};
        this.$scope.form = this.form;
      };

      Admin_TicketLabels_Ctrl_Edit.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.TicketLabelsData.loadList().then(function(recs) {
          _this.labels = recs;
          if (_this.$stateParams.label) {
            _this.label_object = _this.TicketLabelsData.getObjectByLabel(_this.$stateParams.label);
            if (!angular.isUndefined(_this.label_object)) {
              return _this.$scope.form.label = _this.$stateParams.label;
            } else {
              return _this.label_object = {
                label: '',
                count: 0
              };
            }
          }
        });
        return this.$q.all([list_promise]);
      };

      Admin_TicketLabels_Ctrl_Edit.prototype.addNewLabel = function() {
        var _this = this;
        if (!this.form.label) {
          return false;
        }
        this.startSpinner('saving_label');
        return this.Api.sendPost('/ticket_labels', {
          label: this.form.label
        }).success(function() {
          _this.labels.push({
            label: _this.form.label,
            count: 0
          });
          _this.label_object = _this.TicketLabelsData.getObjectByLabel(_this.form.label);
          _this.stopSpinner('saving_label', true).then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_label'));
          });
          _this.skipDirtyState();
          return _this.$state.go('tickets.labels.gocreate');
        }).error(function() {
          return _this.Growl.error(_this.getRegisteredMessage('not_created_label'));
        })["finally"](function() {
          return _this.stopSpinner('saving_label', true);
        });
      };

      Admin_TicketLabels_Ctrl_Edit.prototype.saveLabel = function() {
        var _this = this;
        if (!this.form.label) {
          return false;
        }
        if (!this.label_object.label) {
          return this.addNewLabel();
        }
        this.startSpinner('saving_label');
        return this.Api.sendPost('/ticket_labels/save', {
          label_old: this.label_object.label,
          label_new: this.form.label
        }).success(function() {
          _this.label_object.label = _this.form.label;
          _this.stopSpinner('saving_label', true).then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_label'));
          });
          _this.skipDirtyState();
          return _this.$state.go('tickets.labels');
        }).error(function() {
          return _this.Growl.error(_this.getRegisteredMessage('not_saved_label'));
        })["finally"](function() {
          return _this.stopSpinner('saving_label', true);
        });
      };

      Admin_TicketLabels_Ctrl_Edit.prototype.checkDirtyState = function() {
        if (this.label_object.label && !this.form.label) {
          return true;
        }
        if (this.label_object.label && this.form.label && this.label_object.label !== this.form.label) {
          return true;
        }
        if (!this.label_object.label && this.form.label) {
          return true;
        }
        return false;
      };

      return Admin_TicketLabels_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketLabels_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/