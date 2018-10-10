define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_Settings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_Settings'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = ['$http']

    init: ->
      @form = {
        lang_auto_install: false,
        lang_auto_detect:  false,
        tickets_move_from: 0,
        tickets_move_to:   0,
        users_move_from:   0,
        users_move_to:     0,
        users_move_to:     0,
        download_language: 0
      }

    initialLoad: ->
      promise = @Api.sendDataGet({
        auto_install: '/settings/values/core.lang_auto_install',
        auto_detect:  '/settings/values/core.lang_auto_detect',
        lang:         '/langs'
      }).then( (res) =>
        @form.lang_auto_install = if parseInt(res.data.auto_install.value) then true else false
        @form.lang_auto_detect = if parseInt(res.data.auto_detect.value) then true else false

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
      return if @$scope.form_props && @$scope.form_props.$invalid

      @startSpinner('saving_settings')
      promises = []
      promises.push @Api.sendPost('/settings/values/core.lang_auto_install', {
        value: if @form.lang_auto_install then '1' else '0'
      })
      promises.push @Api.sendPost('/settings/values/core.lang_auto_detect', {
        value: if @form.lang_auto_detect then '1' else '0'
      })

      @$q.all(promises).then(=>
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