define([
  'DeskPRO/Util/Util'
], (Util) => {
  class Admin_ChannelFacebook_FormModel_EditFacebookPageModel {
    constructor(page) {
      this.page = page;

      if (Util.isEmpty(this.page.import_wall_posts)) { this.page.import_wall_posts = true; }
      if (Util.isEmpty(this.page.import_direct_messages)) { this.page.import_direct_messages = true; }
      if (Util.isEmpty(this.page.disable_own_wall_posts)) { this.page.disable_own_wall_posts = true; }

      this.form = { page: {
        id:                     this.page.id || 0,
        name:                   this.page.name || '',
        graph_id:               this.page.graph_id || '',
        page_token:             this.page.page_token || '',
        user_token:             this.page.user_token || '',
        picture_url:            this.page.picture_url || '',
        user_graph_id:          this.page.user_graph_id || '',
        import_wall_posts:      !!this.page.import_wall_posts,
        disable_own_wall_posts: !!this.page.disable_own_wall_posts,
        import_direct_messages: !!this.page.import_direct_messages,
        is_enabled:             !!this.page.is_enabled,
        is_connected:           !!this.page.is_connected,
        is_tested:              !!this.page.is_tested,
        app:                    {
          id:         this.page.app.id || 0,
          app_id:     this.page.app.app_id || '',
          app_secret: this.page.app.app_secret || '',
          name:       this.page.app.name || '',
          logo_url:   this.page.app.logo_url || '',
          icon_url:   this.page.app.icon_url || ''
        },
      }
      };
    }

    getFormData() {
      const form = Util.clone(this.form, true);
      return form.page;
    }
  }
  return Admin_ChannelFacebook_FormModel_EditFacebookPageModel;
});
