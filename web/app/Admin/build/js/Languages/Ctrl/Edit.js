(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_Edit, _ref;
    Admin_Languages_Ctrl_Edit = (function(_super) {
      __extends(Admin_Languages_Ctrl_Edit, _super);

      function Admin_Languages_Ctrl_Edit() {
        _ref = Admin_Languages_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Languages_Ctrl_Edit.CTRL_ID = 'Admin_Languages_Ctrl_Edit';

      Admin_Languages_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Languages_Ctrl_Edit.prototype.init = function() {
        var format;
        this.id = this.$stateParams.id;
        format = function(flag) {
          console.log(flag);
          if (!flag || !flag.text) {
            return '';
          }
          return "<img src='" + DP_ASSET_URL + "/images/flags/" + flag.id.toLowerCase() + "' style='margin-right: 2px;' />" + flag.text;
        };
        return this.$scope.select2Flag = {
          formatResult: format,
          formatSelection: format,
          escapeMarkup: function(m) {
            return m;
          }
        };
      };

      Admin_Languages_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet("/langs/" + this.id).then(function(result) {
          if (!result.data.language) {
            _this.$state.go('setup.languages.install', {
              id: "install-" + _this.id
            });
            return;
          }
          _this.pack = result.data.pack;
          _this.lang = result.data.language;
          return _this.form = {
            title: _this.lang.title,
            flag_image: _this.lang.flag_image,
            locale: _this.lang.locale
          };
        });
        return promise;
      };

      return Admin_Languages_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/