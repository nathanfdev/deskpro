(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/ChannelFacebook/FormModel/EditFacebookPageModel', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Admin_ChannelFacebook_FormModel_EditFacebookPageModel, Util) {
    var Admin_ChannelFacebook_Ctrl_Edit;
    Admin_ChannelFacebook_Ctrl_Edit = (function(_super) {
      __extends(Admin_ChannelFacebook_Ctrl_Edit, _super);

      function Admin_ChannelFacebook_Ctrl_Edit() {
        return Admin_ChannelFacebook_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_ChannelFacebook_Ctrl_Edit.CTRL_ID = 'Admin_ChannelFacebook_Ctrl_Edit';

      Admin_ChannelFacebook_Ctrl_Edit.CTRL_AS = 'ChannelFacebookEdit';

      Admin_ChannelFacebook_Ctrl_Edit.DEPS = ['Api', 'Growl', 'FacebookPagesData', '$stateParams', '$state', '$timeout'];

      Admin_ChannelFacebook_Ctrl_Edit.prototype.init = function() {
        this.pageId = parseInt(this.$stateParams.id || 0);
        this.page = null;
        return this.form_model = null;
      };

      Admin_ChannelFacebook_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/channel/facebook/page/" + this.pageId).then((function(_this) {
          return function(result) {
            if (result.data) {
              _this.page = result.data;
              _this.form_model = new Admin_ChannelFacebook_FormModel_EditFacebookPageModel(_this.page || {});
              return _this.setFormOnScope();
            }
          };
        })(this));
        return promise;
      };

      Admin_ChannelFacebook_Ctrl_Edit.prototype.setFormOnScope = function() {
        this.$scope.form = this.form_model.form;
        return console.log(Util.dump(this.$scope.form));
      };

      Admin_ChannelFacebook_Ctrl_Edit.prototype.savePage = function() {
        var postData;
        this.startSpinner('saving_page');
        postData = {
          page: this.form_model.getFormData()
        };
        console.log("sending data");
        console.log(postData);
        return this.Api.sendPostJson("/channel/facebook/page/" + this.pageId, postData).then((function(_this) {
          return function(result) {
            console.log(result);
            _this.page = result.data;
            _this.FacebookPagesData.updateModel(_this.page);
            _this.Growl.success(_this.getRegisteredMessage('saved_page'));
            _this.stopSpinner('saving_page');
            return _this.$scope.$parent.ChannelFacebookList.pingElement('save_page');
          };
        })(this));
      };

      Admin_ChannelFacebook_Ctrl_Edit.prototype.connect = function() {
        var postData, promise;
        postData = this.getPostData();
        promise = this.Api.sendPostJson("/channel/facebook/connect_provider", postData);
        promise.then((function(_this) {
          return function(result) {
            if (result.data.success) {
              _this.$scope.connection_problem = false;
              _this.page = result.data.account;
              _this.form_model.setAccountData(result.data.account);
              if (_this.pageId) {
                _this.FacebookPagesData.updateModel(_this.page);
              }
              _this.ngApply();
              _this.Growl.success(_this.getRegisteredMessage('connected'));
            } else {
              _this.$scope.connection_problem = true;
              _this.form_model.markConnected(false);
              _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
            }
            return _this.stopSpinner('sms_connect_provider');
          };
        })(this));
        promise.error((function(_this) {
          return function(result) {
            _this.$scope.connection_problem = true;
            _this.form_model.markConnected(false);
            _this.stopSpinner('sms_connect_provider');
            return _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
          };
        })(this));
        this.startSpinner('sms_connect_provider');
        return promise;
      };

      Admin_ChannelFacebook_Ctrl_Edit.prototype.setupAndTest = function() {
        var postData, promise;
        postData = this.getPostData();
        promise = this.Api.sendPostJson("/channel/facebook/setup-and-test/twilio", postData);
        promise.then((function(_this) {
          return function(result) {
            var checkTestStatus;
            if (result) {
              checkTestStatus = function() {
                var url;
                url = "/channel/facebook/page/" + _this.pageId;
                return _this.$timeout(function() {
                  return _this.Api.sendGet(url).then(function(result) {
                    if (result.data.is_tested) {
                      _this.stopSpinner('sms_test_provider');
                      _this.page = result.data;
                      _this.form_model.setAccountData(result.data);
                      _this.FacebookPagesData.updateModel(_this.page);
                      _this.ngApply();
                      return _this.Growl.success(_this.getRegisteredMessage('setup_and_tested_success'));
                    } else {
                      return checkTestStatus();
                    }
                  });
                }, 1000);
              };
              return checkTestStatus();
            } else {
              _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
              return _this.form_model.markTested(false);
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(result) {
            _this.$scope.connection_problem = true;
            _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
            return _this.stopSpinner('sms_test_provider');
          };
        })(this));
        this.startSpinner('sms_test_provider');
        return promise;
      };

      return Admin_ChannelFacebook_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_ChannelFacebook_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
