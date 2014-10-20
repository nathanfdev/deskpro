(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  define(['Admin/Main/Ctrl/Base', 'Admin/ChannelFacebook/FormModel/EditFacebookPageModel'], function(Admin_Ctrl_Base, Admin_ChannelFacebook_FormModel_EditFacebookPageModel) {
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
        this.app_id = '';
        this.app_secret = null;
        this.app_connected = false;
        this.app_name = '';
        this.app_icon_url = '';
        this.app_logo_url = '';
        this.app_credentials_required = false;
        this.user_graph_id = null;
        this.user_access_token = null;
        this.available_user_pages = [];
        this.fb_init = false;
        this.checked_for_pages = false;
        requirejs.config({
          paths: {
            facebook: '//connect.facebook.net/en_US/all'
          },
          shim: {
            facebook: {
              exports: 'FB'
            }
          }
        });
        return require(['facebook'], (function(_this) {
          return function(FB) {
            return _this.FB = FB;
          };
        })(this), function() {
          return console.warn("Failed to load Facebook: connect.facebook.net/en_US/all.js");
        });
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
          this.new_page = {
            name: selected_page.name,
            graph_id: selected_page.id,
            page_token: selected_page.access_token,
            picture_url: selected_page.picture_url,
            user_graph_id: this.user_graph_id,
            user_token: this.user_access_token,
            import_wall_posts: true,
            disable_own_wall_posts: true,
            import_direct_messages: true,
            is_enbled: false,
            is_connected: false,
            is_tested: false,
            app: {
              app_id: this.app_id,
              app_secret: this.app_secret,
              name: this.app_name,
              logo_url: this.app_logo_url,
              icon_url: this.app_icon_url
            }
          };
          postData = {
            page: this.new_page
          };
          return this.Api.sendPostJson('/channel/facebook/pages', postData).then((function(_this) {
            return function(response) {
              if (response.status === 200) {
                _this.available_user_pages = _this.available_user_pages.filter(function(page) {
                  return page.id !== response.data.graph_id;
                });
                _this.new_page_model = new Admin_ChannelFacebook_FormModel_EditFacebookPageModel(response.data || {});
                _this.$scope.$parent.ChannelFacebookList.pingElement('save_page');
                _this.FacebookPagesData.addToList(_this.new_page_model.getFormData());
                return _this.$state.go('tickets.channel_facebook.edit', {
                  id: response.data.id
                });
              } else {
                _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
                _this._stopSpinnerTimeout('connecting_app');
                _this.app_connected = false;
                _this.app_credentials_required = true;
                return _this.checked_for_pages = false;
              }
            };
          })(this))["catch"]((function(_this) {
            return function(data, status) {
              _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
              _this._stopSpinnerTimeout('connecting_app');
              _this.app_connected = false;
              _this.app_credentials_required = true;
              return _this.checked_for_pages = false;
            };
          })(this));
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
                  var authResponse;
                  authResponse = FB.getAuthResponse();
                  _this.user_graph_id = authResponse.userID;
                  _this.user_access_token = authResponse.accessToken;
                  return FB.api("/" + _this.user_graph_id + "/permissions", 'GET', {}, function(perms) {
                    var p, pp, _ref, _ref1;
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
                    if ((_ref = !"manage_pages", __indexOf.call(pp, _ref) >= 0) || (_ref1 = !"read_page_mailboxes", __indexOf.call(pp, _ref1) >= 0)) {
                      _this.Growl.error(_this.getRegisteredMessage('invalid_permissions'));
                      _this._stopSpinnerTimeout('connecting_app');
                    }
                    return FB.login(function(response) {
                      if (response.authResponse) {
                        _this.user_graph_id = response.authResponse.userID;
                        _this.user_access_token = response.authResponse.accessToken;
                      } else {
                        _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
                        _this._stopSpinnerTimeout('connecting_app');
                      }
                      return d.resolve(_this.user_graph_id);
                    }, {
                      scope: 'public_profile,manage_pages,read_page_mailboxes'
                    });
                  });
                });
              } else {
                return FB.login(function(response) {
                  if (response.authResponse) {
                    _this.user_graph_id = response.authResponse.userID;
                    _this.user_access_token = response.authResponse.accessToken;
                  } else {
                    _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
                    _this._stopSpinnerTimeout('connecting_app');
                  }
                  return d.resolve();
                }, {
                  scope: 'public_profile,manage_pages,read_page_mailboxes'
                });
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
              if (__indexOf.call(pp, "manage_pages") >= 0 && __indexOf.call(pp, "read_page_mailboxes") >= 0) {
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
                    category: pg.category,
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
                if (!_this.FacebookPagesData.checkExistsByGraphId(page.id)) {
                  promises.push(callfunc(page));
                }
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
