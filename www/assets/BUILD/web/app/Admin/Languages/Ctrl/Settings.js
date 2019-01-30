define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_Settings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_Settings'
    @CTRL_AS = 'EditCtrl'
    @DEPS    = ['$http', 'LangSyncApi']

    init: ->
      @form = {
        lang_auto_install: false,
        lang_auto_detect:  false,
        tickets_move_from: 0,
        tickets_move_to:   0,
        users_move_from:   0,
        users_move_to:     0,
        download_language: 'all'
      }
      @syncLog = ''

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
              id:          pack.installed_language_id,
              system_name: pack.id,
              title:       pack.title
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

    doDownloadLanguages: ->

      syncLanguage = (nameId, locale) =>
        console.log('[Language sync] Process: ', locale)
        @syncLog += "Downloading " + locale + " ...\n"
        @$q.all([
          @LangSyncApi.getPhrases(locale, 'backend')
          @LangSyncApi.getPhrases(locale, 'user')
        ]).then (res) =>
          #combine user and backend phrases
          postData = {
            phrases: Object.assign({}, res[0].data, res[1].data)
          }

          @syncLog += "Syncing " + locale + " ...\n"
          @Api.sendPostJson("/langs/#{nameId}/phrases/sync", postData)

      @showConfirm('@confirm_download_languages').result.then(=>
        @startSpinner('update_languages')

        @syncLog = ''
        @showLog = true

        @syncLog += "Downloading Manifest ...\n"
        @LangSyncApi.getManifest().then((result) =>
          manifest = result.data
          langs = @langChoices
          if (@form.download_language != 'all')
            langs = [{system_name: @form.download_language}]

          langs = langs.map((l) -> l.system_name)

          deferred = @$q.defer()
          res = deferred.promise

          #queue promises to process languages sync one by one
          manifest.forEach((lang) ->
            if langs.indexOf(lang.id) != -1
              res = res.then(() -> syncLanguage(lang.id, lang.locale))
          )

          deferred.resolve()

          #execute chained promises
          res
            .then(() =>
              @stopSpinner('update_languages')
              @syncLog += "Done.\n"
            )
            .catch((error) =>
              console.log(error)
              @syncLog += "Error. Please check the console.\n"
              @stopSpinner('update_languages')
            )
        ).catch( (error) =>
          console.log(error)
          @syncLog += "Error. Please check the console.\n"
          @stopSpinner('update_languages')
        )
      )

    doResetManagedPhrases: ->

      @showConfirm('@confirm_reset_managed_phrases').result.then(=>
        @startSpinner('update_languages')
        @Api.sendPostJson("/langs/phrases/reset-managed", {}).then( (result) =>
          @stopSpinner('update_languages')
        )
      )

  Admin_Languages_Ctrl_Settings.EXPORT_CTRL()