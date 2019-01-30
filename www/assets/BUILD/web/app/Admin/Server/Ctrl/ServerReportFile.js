define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerReportFile_Ctrl_ServerReportFile extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerReportFile_Ctrl_ServerReportFile'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$window']

    init: ->
      @server_file_check = null

      @total_checks = 0
      @current_check =
      @current_percentage = 0

      @check_started = false
      @check_in_progress = false

      @file_check_results = ''
      @server_file_check_done = false
      @$scope.with_file_check = true

    initialLoad: ->
      data_promise = @Api.sendGet('/server_file_check').then( (res) =>
        @server_file_check = res.data.server_file_check
        @total_checks = @server_file_check.count

        # the case when we have '/app/sys/Resources/distro-checksums.php' deleted
        if @total_checks == 1
          @current_check = -1
          @doNextRequest()
      )

      return @$q.all([data_promise])

    ###
    # Starting the process of integrity file check
    ###
    startCheck: ->
      @current_check = 0
      @current_percentage = 0
      @check_started = true
      @check_in_progress = true
      @file_check_results = ''
      @doNextRequest()

    ###
    # Execute AJAX request to next batch of files
    ###
    doNextRequest: ->
      @current_check++

      if @current_check <= @total_checks and @$scope.with_file_check and @file_check_results.length < 153600
        @Api.sendGet('/server_file_check/' + (@current_check-1)).then((res) =>
          data = res.data.server_file_check

          if data.okay
            @file_check_results += 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + data.okay.length + ' files verified' + "\n"

          if data.added
            for file in data.added
              @file_check_results += 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + ' File added: ' + file + "\n"

          if data.changed and data.changed.length
            for file in data.changed
              @file_check_results += 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + ' File changed: ' + file + "\n"

          if data.removed and data.removed.length
            for file in data.removed
              @file_check_results += 'Batch ' + @current_check + ' of ' + @total_checks + ': ' + ' Missing: ' + file + "\n"

          @current_percentage = Math.ceil @current_check / @total_checks * 100

          @doNextRequest()
        )
      else
        if @file_check_results >= 153600
          @file_check_results = @file_check_results.substring(0, 153600) + "\n\n(Too many changes detected, results truncated)"

        @check_in_progress = false
        @current_percentage = 100
        @server_file_check_done = true
        @redirectToReportFile()

    ###
    # After we've donw with file integrity checking we coudl redirect user to actual report file
    ###
    redirectToReportFile: ->
      @Api.sendPost('/server_report_file/file_check_results', {file_check_results: @file_check_results}).then( (res) =>
        @$scope.download_link = window.DP_BASE_API_URL + '/server_report_file?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN
      )

  Admin_ServerReportFile_Ctrl_ServerReportFile.EXPORT_CTRL()