define [
  'DeskPRO/Util/Util'
], (Util) ->
  class Admin_ChannelFacebook_FormModel_EditFacebookPageModel
    constructor: (page) ->
      @page = page

      if Util.isEmpty(@page.import_wall_posts) then @page.import_wall_posts = true
      if Util.isEmpty(@page.import_direct_messages) then @page.import_direct_messages = true
      if Util.isEmpty(@page.disable_own_wall_posts) then @page.disable_own_wall_posts = true

      @form = { page: {
          id: @page.id || 0
          name: @page.name || '',
          graph_id: @page.graph_id || '',
          page_token: @page.page_token || '',
          user_token: @page.user_token || '',
          picture_url: @page.picture_url || '',
          user_graph_id: @page.user_graph_id || '',
          import_wall_posts: if @page.import_wall_posts then true else false,
          disable_own_wall_posts: if @page.disable_own_wall_posts then true else false,
          import_direct_messages: if @page.import_direct_messages then true else false,
          is_enabled: if @page.is_enabled then true else false,
          is_connected: if @page.is_connected then true else false,
          is_tested: if @page.is_tested then true else false,
          app: {
            id: @page.app.id || 0
            app_id: @page.app.app_id || '',
            app_secret: @page.app.app_secret || '',
            name: @page.app.name || '',
            logo_url: @page.app.logo_url || '',
            icon_url: @page.app.icon_url || ''
          },
        }
      }

    getFormData: ->
      form = Util.clone(@form, true)
      return form.page
