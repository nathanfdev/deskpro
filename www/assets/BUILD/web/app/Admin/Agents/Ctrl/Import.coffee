define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Agents_Ctrl_Import extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Agents_Ctrl_Import'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$http', '$upload', 'DpLicense']

    init: ->
      @busy = false
      @restart()
      @$scope.fileUploadOptions = {url: @$http.formatApiUrl('/import_csv_upload') }



    uploadFiles: (files) ->
      @busy = true
      @page = 1

      # expected only 1 file
      for file in files
        @$upload.upload({url: @$scope.fileUploadOptions.url, file: file}).then(
          (data) =>
            @sendEmails data.data.filename
          () =>
            @busy = false
            console.log 'error'
        )



    restart: ->
      @page = 0 # import page layout number
      @emails = []
      @results = []
      @invited = 0



    sendEmails: (filename) ->
      @busy = true

      agents = {}
      @emails.map (email) =>
        agents[email] = {email: email} # make unique

      @Api.sendPostJson('/agents_bulk/check', {agents: agents, filename: filename}).then( (res) =>
        @busy = false
        if res.data.need_plan
          @DpLicense.openUpgradeLicense('upgrade_plan').then(=>
            @doSendEmails(filename)
          )
        else
          @doSendEmails(filename)
      , =>
        @busy = false
      )

    doSendEmails: (filename) ->
      @busy = true
      @page = 1

      agents = {}
      @emails.map (email) =>
        agents[email] = {email: email} # make unique

      @Api.sendPostJson('/agents_bulk', {agents: agents, filename: filename}).then(
        (data) =>
          @busy = false

          return if data.data.length? # catch array instead of object, possible if no results

          for email, entry of data.data
            @invited++ if entry.person_id?

            if 'validation_error' == entry.error_code
              message = entry.error_message
              if entry.errors?.errors?
                message = ''
                entry.errors.errors.map (error) -> message += (error.message + ' ')
              entry = {error: message}

            entry._email = email
            @results.push entry

        () =>
          @busy = false
      )

    submitEmails: ->
      if !@$scope.Form.$valid then return false
      @sendEmails()


  Admin_Agents_Ctrl_Import.EXPORT_CTRL()