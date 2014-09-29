(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackCategories_Ctrl_Edit;
    Admin_FeedbackCategories_Ctrl_Edit = (function(_super) {
      __extends(Admin_FeedbackCategories_Ctrl_Edit, _super);

      function Admin_FeedbackCategories_Ctrl_Edit() {
        return Admin_FeedbackCategories_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackCategories_Ctrl_Edit.CTRL_ID = 'Admin_FeedbackCategories_Ctrl_Edit';

      Admin_FeedbackCategories_Ctrl_Edit.CTRL_AS = 'FeedbackCategoriesEdit';

      Admin_FeedbackCategories_Ctrl_Edit.DEPS = ['Api', 'Growl', 'FeedbackCategoriesData', '$stateParams', '$modal'];

      Admin_FeedbackCategories_Ctrl_Edit.prototype.init = function() {
        this.feedback_category = {};
        this.feedback_categories_parent_list = {};
        this.addManagedListener(this.FeedbackCategoriesData.recs, 'changed', (function(_this) {
          return function() {
            _this.feedback_categories_parent_list = _this.FeedbackCategoriesData.getListOfParents(_this.feedback_category);
            return _this.ngApply();
          };
        })(this));
      };

      Admin_FeedbackCategories_Ctrl_Edit.prototype.initialLoad = function() {
        var promise, requests;
        requests = [
          this.FeedbackCategoriesData.loadList(), this.$stateParams.id ? this.Api.sendDataGet({
            feedback_category: '/feedback_categories/' + this.$stateParams.id
          }) : void 0
        ];
        promise = this.$q.all(requests).then((function(_this) {
          return function(result) {
            if (_this.$stateParams.id) {
              _this.feedback_category = result[1].data.feedback_category.feedback_category;
              return _this.feedback_categories_parent_list = _this.FeedbackCategoriesData.getListOfParents(_this.feedback_category);
            }
          };
        })(this));
        return promise;
      };


      /*
      			 * Saves the current form
      			 *
      			 * @return {promise}
       */

      Admin_FeedbackCategories_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.feedback_category.id;
        this.startSpinner('saving_feedback_category');
        if (is_new) {
          promise = this.Api.sendPutJson('/feedback_categories', {
            feedback_category: this.feedback_category
          });
        } else {
          promise = this.Api.sendPostJson('/feedback_categories/' + this.feedback_category.id, {
            feedback_category: this.feedback_category
          });
        }
        promise.success((function(_this) {
          return function(result) {
            _this.feedback_category.id = result.id;
            _this.stopSpinner('saving_feedback_category', true).then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_feedback_category'));
            });
            _this.FeedbackCategoriesData.updateModel(_this.feedback_category);
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('portal.feedback_categories.gocreate');
            } else {
              return _this.$state.go('portal.feedback_categories');
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving_feedback_category', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };

      return Admin_FeedbackCategories_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackCategories_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
