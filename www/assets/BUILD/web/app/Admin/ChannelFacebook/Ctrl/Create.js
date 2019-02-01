define([
  'Admin/Main/Ctrl/Base',
  'Admin/ChannelFacebook/FormModel/EditFacebookPageModel',
], function(
  Admin_Ctrl_Base,
  Admin_ChannelFacebook_FormModel_EditFacebookPageModel
) {
  class Admin_ChannelFacebook_Ctrl_Create extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChannelFacebook_Ctrl_Create';
      this.CTRL_AS = 'ChannelFacebookCreate';
      this.DEPS    = ['Api', 'Growl', 'FacebookPagesData', '$stateParams', '$state', '$timeout'];
    }

    init() {
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
      // initial load MUST contain curent page ids so that we only display new ones

      requirejs.config({
        paths: { facebook: '//connect.facebook.net/en_US/all' },
        shim:  { facebook: { exports: 'FB' } }
      });

      return require(['facebook'], FB => {
        return this.FB = FB;
      }
      , () => console.warn("Failed to load Facebook: connect.facebook.net/en_US/all.js"));
    }

    appConnect() {
      if (!this.app_id || !this.app_secret) {
        return this.app_credentials_required = true;
      } else {
        this.app_credentials_required = false;
        this.startSpinner('connecting_app');
        return this._getPages().then(() => {
          this.Growl.success(this.getRegisteredMessage('connected'));
          return this._stopSpinnerTimeout('connecting_app');
        }
        , error => {
          this.Growl.error(this.getRegisteredMessage('connected_fail'));
          return this._stopSpinnerTimeout('connecting_app');
        });
      }
    }

    selectPage(page_id) {
      let selected_page = null;
      for (let page of Array.from(this.available_user_pages)) {
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
          },
        };

        const postData = {
          page: this.new_page
        };

        return this.Api.sendPostJson('/channel/facebook/pages', postData).then(response => {
          if (response.status === 200) {
            this.available_user_pages = this.available_user_pages.filter(page => page.id !== response.data.graph_id);
            this.new_page_model = new Admin_ChannelFacebook_FormModel_EditFacebookPageModel(response.data || {});
            this.$scope.$parent.ChannelFacebookList.pingElement('save_page');
            this.FacebookPagesData.addToList(this.new_page_model.getFormData());
            return this.$state.go('tickets.channel_facebook.edit', { id: response.data.id });
          } else {
            this.Growl.error(this.getRegisteredMessage('connected_fail'));
            this._stopSpinnerTimeout('connecting_app');
            this.app_connected = false;
            this.app_credentials_required = true;
            return this.checked_for_pages = false;
          }

        }).catch((data, status) => {
          this.Growl.error(this.getRegisteredMessage('connected_fail'));
          this._stopSpinnerTimeout('connecting_app');
          this.app_connected = false;
          this.app_credentials_required = true;
          return this.checked_for_pages = false;
        });
      }
    }

    _getPages() {
      const d2 = this.$q.defer();

      const start = () => {
        const d = this.$q.defer();
        this._FBinit();
        FB.getLoginStatus(response => {
          if (response.status === 'connected') {
            return FB.api('/me', 'GET', {}, me => {
              const authResponse = FB.getAuthResponse();
              this.user_graph_id = authResponse.userID;
              this.user_access_token = authResponse.accessToken;
              return FB.api(`/${this.user_graph_id}/permissions`, 'GET', {}, perms => {
                const pp = (Array.from(perms.data).map((p) => p.permission));
                if (Array.from(pp).includes(!"manage_pages") || Array.from(pp).includes(!"read_page_mailboxes")) {
                  this.Growl.error(this.getRegisteredMessage('invalid_permissions'));
                  this._stopSpinnerTimeout('connecting_app');
                }

                return FB.login(response => {
                  if (response.authResponse) {
                    this.user_graph_id = response.authResponse.userID;
                    this.user_access_token = response.authResponse.accessToken;
                  } else {
                    this.Growl.error(this.getRegisteredMessage('connected_fail'));
                    this._stopSpinnerTimeout('connecting_app');
                  }
                  return d.resolve(this.user_graph_id);
                }
                , {
                    scope: 'public_profile,manage_pages,read_page_mailboxes'
                  }
                );
              });
            });
          } else {
            return FB.login(response => {
              if (response.authResponse) {
                this.user_graph_id = response.authResponse.userID;
                this.user_access_token = response.authResponse.accessToken;
              } else {
                this.Growl.error(this.getRegisteredMessage('connected_fail'));
                this._stopSpinnerTimeout('connecting_app');
              }
              return d.resolve();
            }
            , {
                scope: 'public_profile,manage_pages,read_page_mailboxes'
              }
            );
          }
        });
        return d.promise;
      };

      start().then(() => {
        return FB.api(`/${this.app_id}`, 'GET', {}, res => {
          this.app_icon_url = res.icon_url;
          this.app_logo_url = res.logo_url;
          return this.app_name = res.name;
        });
      }
      , error => {
        this.Growl.error(this.getRegisteredMessage('connected_fail'));
        return this._stopSpinnerTimeout('connecting_app');
      }).then(() => {
        return FB.api(`/${this.user_graph_id}/permissions`, 'GET', {}, perms => {
          const pp = (Array.from(perms.data).map((p) => p.permission));
          if (Array.from(pp).includes("manage_pages") && Array.from(pp).includes("read_page_mailboxes")) {
            return this.checked_for_pages = true;
          } else {
            this.Growl.error(this.getRegisteredMessage('invalid_permissions'));
            return this._stopSpinnerTimeout('connecting_app');
          }
        });
      }).then(() => {
        return FB.api(`/${this.user_graph_id}/accounts`, 'GET', {}, pages => {
          // check ot see if already a channel, dont show if so
          this.app_connected = true;
          this.available_user_pages = [];
          const callfunc = pg => {
            const d3 = this.$q.defer();
            FB.api(`/${pg.id}/picture`, 'GET', {}, pinfo => {
              this.available_user_pages.push({
                id: pg.id,
                access_token: pg.access_token,
                picture_url: pinfo.data.url,
                category: pg.category,
                name: pg.name,
              });
              return d3.resolve();
            });
            return d3.promise;
          };

          const promises = [];
          for (let page of Array.from(pages.data)) {
            if (!this.FacebookPagesData.checkExistsByGraphId(page.id)) {
              promises.push(callfunc(page));
            }
          }

          return d2.resolve(this.$q.all(promises));
        });
      }
      , error => {
        this.Growl.error(this.getRegisteredMessage('connected_fail'));
        return this._stopSpinnerTimeout('connecting_app');
      });


      return d2.promise;
    }

    _stopSpinnerTimeout(name) {
      return this.$timeout(() => {
        return this.stopSpinner(name, true);
      }
      , 0);
    }

    _FBinit() {
      if (!this.fb_init) {
        return FB.init({
          appId: this.app_id,
          xfbml: true,
          version: 'v2.1'
        });
      }
    }

    initialLoad() {
    }
  }
  Admin_ChannelFacebook_Ctrl_Create.initClass();



  return Admin_ChannelFacebook_Ctrl_Create.EXPORT_CTRL();
});
