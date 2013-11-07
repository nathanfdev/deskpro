(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackCategories_Ctrl_List, _ref;
    Admin_FeedbackCategories_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackCategories_Ctrl_List, _super);

      function Admin_FeedbackCategories_Ctrl_List() {
        _ref = Admin_FeedbackCategories_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_FeedbackCategories_Ctrl_List.CTRL_ID = 'Admin_FeedbackCategories_Ctrl_List';

      Admin_FeedbackCategories_Ctrl_List.CTRL_AS = 'FeedbackCategoriesList';

      Admin_FeedbackCategories_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackCategoriesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackCategories_Ctrl_List.CTRL_TYPE = 'list';

      Admin_FeedbackCategories_Ctrl_List.prototype.init = function() {
        this.feedback_categories = [];
        this.parent_data = [];
        return this.child_data = {};
      };

      Admin_FeedbackCategories_Ctrl_List.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.FeedbackCategoriesData.loadList().then(function(recs) {
          _this.initHierarchyData(recs.values());
          return _this.addManagedListener(_this.FeedbackCategoriesData.recs, 'changed', function() {
            _this.initHierarchyData(_this.FeedbackCategoriesData.recs.values());
            return _this.ngApply();
          });
        });
        return this.$q.all([list_promise]);
      };

      Admin_FeedbackCategories_Ctrl_List.prototype.initHierarchyData = function(feedback_categories) {
        var category, _i, _len, _results;
        this.feedback_categories = feedback_categories;
        _results = [];
        for (_i = 0, _len = feedback_categories.length; _i < _len; _i++) {
          category = feedback_categories[_i];
          if (category.parent_id) {
            if (!this.child_data[category.parent_id]) {
              this.child_data[category.parent_id] = [];
            }
            _results.push(this.child_data[category.parent_id].push(category));
          } else {
            _results.push(this.parent_data.push(category));
          }
        }
        return _results;
      };

      return Admin_FeedbackCategories_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackCategories_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/