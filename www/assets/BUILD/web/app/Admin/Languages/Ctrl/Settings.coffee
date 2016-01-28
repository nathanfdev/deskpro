define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_Settings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_Settings'
    @CTRL_AS = 'EditCtrl'

    init: ->
      @form = {
        lang_auto_install: false,
        tickets_move_from: 0,
        tickets_move_to:   0,
        users_move_from:   0,
        users_move_to:     0,
      }

    initialLoad: ->
      promise = @Api.sendDataGet({
        setting: '/settings/values/core.lang_auto_install',
        lang:    '/langs'
      }).then( (res) =>
        @form.lang_auto_install = if parseInt(res.data.setting.value) then true else false

        @langChoices  = []
        for pack in res.data.lang.packs
          if pack.is_installed
            @langChoices.push({
              id:    pack.installed_language_id,
              title: pack.title
            })

        @form.tickets_move_to = res.data.lang.default_lang_id
        @form.users_move_to = res.data.lang.default_lang_id
      )
      return promise

    saveSettings: ->
      console.info 'test'
      return if @$scope.form_props.$invalid

      @startSpinner('saving_settings')
      @Api.sendPost('/settings/values/core.lang_auto_install', {
        value: if @form.lang_auto_install then '1' else '0'
      }).then(=>
        @stopSpinner('saving_settings')
      )

    doMassTicketMove: ->
      @showConfirm('@confirm_move_tickets').result.then(=>
        @startSpinner('saving_tickets')
        postData = {
          from_lang: @form.tickets_move_from,
          to_lang:   @form.tickets_move_to
        }
        @Api.sendPost('/langs/tools/mass-update-tickets', postData).then(=>
          @stopSpinner('saving_tickets')
        )
      )

    doMassUserMove: ->
      @showConfirm('@confirm_move_users').result.then(=>
        @startSpinner('saving_users')
        postData = {
          from_lang: @form.users_move_from,
          to_lang:   @form.users_move_to
        }
        @Api.sendPost('/langs/tools/mass-update-users', postData).then(=>
          @stopSpinner('saving_users')
        )
      )


  Admin_Languages_Ctrl_Settings.EXPORT_CTRL()