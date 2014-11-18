define ->
  ###
   # Description
   # -----------
   #
    # This directive could be used for sending of periodic requests to server via AJAX
    # and updating the status of some operation using data retrieved from these periodic requests
    #
    # Data retrieved from server should be in any format of single JSON object
    # but should contain 'status' and 'message' keys:
    #
    # {
    #   status: 'pending' / 'table' / 'completed' / 'error',
    #   message: 'some text message describing the state of process, which will be presented to user',
    # }
    #
    # Example View
    # ------------
    # <span dp-status-update="/some_process_status"
    #         status-update-conditions="process_started"
    #         status-update-default-message="Waiting to start process..."
    #         status-update-completed-growl-message="Process finished"
    #           status-update-interval="10000"
    #           status-update-immediate="true">
    # </span>
    #
    # Parameters
    # ------------
    # 1) 'dp-status-update' (required parameter) - API URL which will be used for periodic requests to server
    # 2) 'status-update-conditions' (required parameter) - some scope expression that is used to start process of sending periodic requests to server
    # 3) 'status-update-default-message' (optional parameter, by default empty string) - will be used as default status text in this directive
    # 4) 'status-update-completed-growl-message' (optional parameter, by default null) - used to show Growl message if process finished (if needed)
    # 5) 'status-update-interval' (optional parameter, by default 5000) - how often requests will be sent to server (in ms)
    # 6) 'status-update-immediate' (optional paremeter, by default true) - could be used in cases when you it's needed not to start update immediately
   #
  ###
  Admin_Main_Directive_DpStatusUpdate = ['Api', 'Growl', (Api, Growl) ->
    return {
      restrict: 'AE',
      template: "<i class='spinner-xsmall' ng-show='update_in_progress'></i>{{status_update_message}}",
      link: (scope, element, attrs) ->

        statusUpdateUrl = attrs.dpStatusUpdate
        updateInterval = if attrs.statusUpdateInterval then attrs.statusUpdateInterval else 5000
        defaultMessage = if attrs.statusUpdateDefaultMessage then attrs.statusUpdateDefaultMessage else ''
        updateCompletedGrowlMessage = if attrs.statusUpdateCompletedGrowlMessage then attrs.statusUpdateCompletedGrowlMessage else null

        updateImmediate = if attrs.statusUpdateImmediate then attrs.statusUpdateImmediate = (attrs.statusUpdateImmediate == 'true') else true

        scope.status_update_message = defaultMessage
        scope.update_in_progress = false

        # these are backend statuses when we won't clear interval - this means that requests will be continued
        # for all other statuses - interval will be cleared and requesting the backend will be stopped

        validStatuses = ['pending', 'progress'];

        scope.$watch(attrs.statusUpdateConditions, (newVal, oldVal) =>

          scope.update_in_progress = true

          doGetRequest = (immediate) ->

            Api.sendGet(statusUpdateUrl).then((res) =>

              data = res.data

              status = data.status
              message = data.message

              scope.status_update_message = message
              scope.$emit 'dp-status-update', data

              if validStatuses.indexOf(status) == -1

                if !immediate and updateCompletedGrowlMessage and status == 'completed' then Growl.success(updateCompletedGrowlMessage)
                scope.update_in_progress = false
                clearInterval(interval)
            )

          interval = setInterval(doGetRequest, updateInterval)

          if updateImmediate then doGetRequest(true)
        );
    }
  ]

  return Admin_Main_Directive_DpStatusUpdate