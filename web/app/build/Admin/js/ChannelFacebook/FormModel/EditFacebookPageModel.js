(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_ChannelFacebook_FormModel_EditFacebookPageModel;
    return Admin_ChannelFacebook_FormModel_EditFacebookPageModel = (function() {
      function Admin_ChannelFacebook_FormModel_EditFacebookPageModel(page) {
        this.page = page;
        if (Util.isEmpty(this.page.import_wall_posts)) {
          this.page.import_wall_posts = true;
        }
        if (Util.isEmpty(this.page.import_direct_messages)) {
          this.page.import_direct_messages = true;
        }
        if (Util.isEmpty(this.page.disable_own_wall_posts)) {
          this.page.disable_own_wall_posts = true;
        }
        this.form = {
          page: {
            id: this.page.id || 0,
            name: this.page.name || '',
            graph_id: this.page.graph_id || '',
            page_token: this.page.page_token || '',
            user_token: this.page.user_token || '',
            picture_url: this.page.picture_url || '',
            user_graph_id: this.page.user_graph_id || '',
            import_wall_posts: this.page.import_wall_posts ? true : false,
            disable_own_wall_posts: this.page.disable_own_wall_posts ? true : false,
            import_direct_messages: this.page.import_direct_messages ? true : false,
            is_enabled: this.page.is_enabled ? true : false,
            is_connected: this.page.is_connected ? true : false,
            is_tested: this.page.is_tested ? true : false,
            app: {
              id: this.page.app.id || 0,
              app_id: this.page.app.app_id || '',
              app_secret: this.page.app.app_secret || '',
              name: this.page.app.name || '',
              logo_url: this.page.app.logo_url || '',
              icon_url: this.page.app.icon_url || ''
            }
          }
        };
      }

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.getFormData = function() {
        var form;
        form = Util.clone(this.form, true);
        return form.page;
      };

      return Admin_ChannelFacebook_FormModel_EditFacebookPageModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditFacebookPageModel.js.map
