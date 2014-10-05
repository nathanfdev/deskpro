(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  define(['Admin/Main/Ctrl/Base', 'Admin/ChannelFacebook/FormModel/EditFacebookPageModel', 'facebook'], function(Admin_Ctrl_Base, Admin_ChannelFacebook_FormModel_EditFacebookPageModel, FB) {
    var Admin_ChannelFacebook_Ctrl_Create;
    Admin_ChannelFacebook_Ctrl_Create = (function(_super) {
      __extends(Admin_ChannelFacebook_Ctrl_Create, _super);

      function Admin_ChannelFacebook_Ctrl_Create() {
        return Admin_ChannelFacebook_Ctrl_Create.__super__.constructor.apply(this, arguments);
      }

      Admin_ChannelFacebook_Ctrl_Create.CTRL_ID = 'Admin_ChannelFacebook_Ctrl_Create';

      Admin_ChannelFacebook_Ctrl_Create.CTRL_AS = 'ChannelFacebookCreate';

      Admin_ChannelFacebook_Ctrl_Create.DEPS = ['Api', 'Growl', 'FacebookPagesData', '$stateParams', '$state', '$timeout'];

      Admin_ChannelFacebook_Ctrl_Create.prototype.init = function() {
        this.app_id = '1496820407237522';
        this.app_secret = null;
        this.app_connected = false;
        this.app_name = '';
        this.app_icon_url = '';
        this.app_logo_url = '';
        this.app_credentials_required = false;
        this.user_graph_id = null;
        this.available_user_pages = [];
        this.fb_init = false;
        return this.checked_for_pages = false;
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype.appConnect = function() {
        if (!this.app_id || !this.app_secret) {
          return this.app_credentials_required = true;
        } else {
          this.app_credentials_required = false;
          this.startSpinner('connecting_app');
          return this._getPages().then((function(_this) {
            return function() {
              _this.Growl.success(_this.getRegisteredMessage('connected'));
              return _this._stopSpinnerTimeout('connecting_app');
            };
          })(this), (function(_this) {
            return function(error) {
              _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
              return _this._stopSpinnerTimeout('connecting_app');
            };
          })(this));
        }
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype.selectPage = function(page_id) {
        var page, postData, selected_page, _i, _len, _ref;
        selected_page = null;
        _ref = this.available_user_pages;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          page = _ref[_i];
          if (page.id === page_id) {
            selected_page = page;
            break;
          }
        }
        if (selected_page) {
          postData = {
            app: {
              id: this.app_id,
              name: this.app_name,
              secret: this.app_secret,
              logo_url: this.app_logo_url,
              icon_url: this.app_icon_url
            },
            page: {
              id: selected_page.id,
              access_token: selected_page.access_token,
              picture_url: selected_page.picture_url,
              catgory: selected_page.category,
              name: selected_page.name
            }
          };
          console.log("POST DATA HERE:");
          return console.log(postData);
        }
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype._getPages = function() {
        var d2, start;
        d2 = this.$q.defer();
        start = (function(_this) {
          return function() {
            var d;
            d = _this.$q.defer();
            _this._FBinit();
            FB.getLoginStatus(function(response) {
              if (response.status === 'connected') {
                return FB.api('/me', 'GET', {}, function(me) {
                  _this.user_graph_id = me.id;
                  return d.resolve(_this.user_graph_id);
                });
              } else {
                return _this._login();
              }
            });
            return d.promise;
          };
        })(this);
        start().then((function(_this) {
          return function() {
            return FB.api("/" + _this.app_id, 'GET', {}, function(res) {
              _this.app_icon_url = res.icon_url;
              _this.app_logo_url = res.logo_url;
              return _this.app_name = res.name;
            });
          };
        })(this), (function(_this) {
          return function(error) {
            _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
            return _this._stopSpinnerTimeout('connecting_app');
          };
        })(this)).then((function(_this) {
          return function() {
            return FB.api("/" + _this.user_graph_id + "/permissions", 'GET', {}, function(perms) {
              var p, pp;
              pp = (function() {
                var _i, _len, _ref, _results;
                _ref = perms.data;
                _results = [];
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  p = _ref[_i];
                  _results.push(p.permission);
                }
                return _results;
              })();
              if (__indexOf.call(pp, "manage_pages") >= 0) {
                return _this.checked_for_pages = true;
              } else {
                _this.Growl.error(_this.getRegisteredMessage('invalid_permissions'));
                return _this._stopSpinnerTimeout('connecting_app');
              }
            });
          };
        })(this)).then((function(_this) {
          return function() {
            return FB.api("/" + _this.user_graph_id + "/accounts", 'GET', {}, function(pages) {
              var callfunc, page, promises, _i, _len, _ref;
              _this.app_connected = true;
              _this.available_user_pages = [];
              callfunc = function(pg) {
                var d3;
                d3 = _this.$q.defer();
                FB.api("/" + pg.id + "/picture", 'GET', {}, function(pinfo) {
                  _this.available_user_pages.push({
                    id: pg.id,
                    access_token: pg.access_token,
                    picture_url: pinfo.data.url,
                    catgory: pg.category,
                    name: pg.name
                  });
                  return d3.resolve();
                });
                return d3.promise;
              };
              promises = [];
              _ref = pages.data;
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                page = _ref[_i];
                promises.push(callfunc(page));
              }
              return d2.resolve(_this.$q.all(promises));
            });
          };
        })(this), (function(_this) {
          return function(error) {
            _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
            return _this._stopSpinnerTimeout('connecting_app');
          };
        })(this));
        return d2.promise;
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype._login = function() {
        return FB.login((function(_this) {
          return function(response) {
            if (response.authResponse) {
              _this.user_graph_id = response.authResponse.userID;
              return _this.user_access_token = response.authResponse.accessToken;
            } else {
              _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
              return _this._stopSpinnerTimeout('connecting_app');
            }
          };
        })(this), {
          scope: 'public_profile,manage_pages'
        });
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype._stopSpinnerTimeout = function(name) {
        return this.$timeout((function(_this) {
          return function() {
            return _this.stopSpinner(name, true);
          };
        })(this), 0);
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype._FBinit = function() {
        if (!this.fb_init) {
          return FB.init({
            appId: this.app_id,
            xfbml: true,
            version: 'v2.1'
          });
        }
      };

      Admin_ChannelFacebook_Ctrl_Create.prototype.initialLoad = function() {};

      return Admin_ChannelFacebook_Ctrl_Create;

    })(Admin_Ctrl_Base);
    return Admin_ChannelFacebook_Ctrl_Create.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Create.js.map
