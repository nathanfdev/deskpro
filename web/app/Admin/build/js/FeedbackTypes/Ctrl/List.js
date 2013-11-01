(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackTypes_Ctrl_List, _ref;
    Admin_FeedbackTypes_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackTypes_Ctrl_List, _super);

      function Admin_FeedbackTypes_Ctrl_List() {
        _ref = Admin_FeedbackTypes_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_FeedbackTypes_Ctrl_List.CTRL_ID = 'Admin_FeedbackTypes_Ctrl_List';

      Admin_FeedbackTypes_Ctrl_List.CTRL_AS = 'FeedbackTypesList';

      Admin_FeedbackTypes_Ctrl_List.DEPS = ['$rootScope', '$scope', 'FeedbackTypesData', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackTypes_Ctrl_List.CTRL_TYPE = 'list';

      Admin_FeedbackTypes_Ctrl_List.prototype.init = function() {
        return this.feedback_types = [];
      };

      Admin_FeedbackTypes_Ctrl_List.prototype.initialLoad = function() {
        var list_promise,
          _this = this;
        list_promise = this.FeedbackTypesData.loadList().then(function(recs) {
          _this.feedback_types = recs.values();
          return _this.addManagedListener(_this.FeedbackTypesData.recs, 'changed', function() {
            _this.feedback_types = _this.FeedbackTypesData.recs.values();
            return _this.ngApply();
          });
        });
        return this.$q.all([list_promise]);
      };

      return Admin_FeedbackTypes_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackTypes_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/