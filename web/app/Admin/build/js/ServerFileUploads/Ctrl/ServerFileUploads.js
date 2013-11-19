(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerFileUploads_Ctrl_ServerFileUploads, _ref;
    Admin_ServerFileUploads_Ctrl_ServerFileUploads = (function(_super) {
      __extends(Admin_ServerFileUploads_Ctrl_ServerFileUploads, _super);

      function Admin_ServerFileUploads_Ctrl_ServerFileUploads() {
        _ref = Admin_ServerFileUploads_Ctrl_ServerFileUploads.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_ID = 'Admin_ServerFileUploads_Ctrl_ServerFileUploads';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.CTRL_AS = 'Ctrl';

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.DEPS = [];

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.init = function() {
        return this.$scope.data = null;
      };

      Admin_ServerFileUploads_Ctrl_ServerFileUploads.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet('/server_file_uploads').then(function(res) {
          return _this.$scope.data = res.data.server_file_uploads;
        });
        return this.$q.all([data_promise]);
      };

      return Admin_ServerFileUploads_Ctrl_ServerFileUploads;

    })(Admin_Ctrl_Base);
    return Admin_ServerFileUploads_Ctrl_ServerFileUploads.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ServerFileUploads.js.map
*/