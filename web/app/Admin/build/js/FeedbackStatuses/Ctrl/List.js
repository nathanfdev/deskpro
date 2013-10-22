(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackStatuses_Ctrl_List, _ref;
    Admin_FeedbackStatuses_Ctrl_List = (function(_super) {
      __extends(Admin_FeedbackStatuses_Ctrl_List, _super);

      function Admin_FeedbackStatuses_Ctrl_List() {
        _ref = Admin_FeedbackStatuses_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_FeedbackStatuses_Ctrl_List.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List';

      Admin_FeedbackStatuses_Ctrl_List.CTRL_AS = 'FeedbackStatusesList';

      Admin_FeedbackStatuses_Ctrl_List.DEPS = ['$rootScope', '$scope', 'em', 'Api', '$state', 'Growl'];

      Admin_FeedbackStatuses_Ctrl_List.CTRL_TYPE = 'list';

      Admin_FeedbackStatuses_Ctrl_List.prototype.init = function() {
        this.feedback_statuses_count = 0;
        return this.feedback_stasus_settings = {};
      };

      return Admin_FeedbackStatuses_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/