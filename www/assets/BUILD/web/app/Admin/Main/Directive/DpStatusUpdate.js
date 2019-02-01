define(() => {
  /*
   * Description
   * -----------
   *
    * This directive could be used for sending of periodic requests to server via AJAX
    * and updating the status of some operation using data retrieved from these periodic requests
    *
    * Data retrieved from server should be in any format of single JSON object
    * but should contain 'status' and 'message' keys:
    *
    * {
    *   status: 'pending' / 'table' / 'completed' / 'error',
    *   message: 'some text message describing the state of process, which will be presented to user',
    * }
    *
    * Example View
    * ------------
    * <span dp-status-update="/some_process_status"
    *         status-update-conditions="process_started"
    *         status-update-default-message="Waiting to start process..."
    *         status-update-completed-growl-message="Process finished"
    *           status-update-interval="10000"
    *           status-update-immediate="true">
    * </span>
    *
    * Parameters
    * ------------
    * 1) 'dp-status-update' (required parameter) - API URL which will be used for periodic requests to server
    * 2) 'status-update-conditions' (required parameter) - some scope expression that is used to start process of sending periodic requests to server
    * 3) 'status-update-default-message' (optional parameter, by default empty string) - will be used as default status text in this directive
    * 4) 'status-update-completed-growl-message' (optional parameter, by default null) - used to show Growl message if process finished (if needed)
    * 5) 'status-update-interval' (optional parameter, by default 5000) - how often requests will be sent to server (in ms)
    * 6) 'status-update-immediate' (optional paremeter, by default true) - could be used in cases when you it's needed not to start update immediately
   *
  */
  const Admin_Main_Directive_DpStatusUpdate = ['Api', 'Growl', (Api, Growl) =>
    ({
      restrict: 'AE',
      template: "<i class='spinner-xsmall' ng-show='update_in_progress'></i>{{status_update_message}}",
      link(scope, element, attrs) {
        const statusUpdateUrl = attrs.dpStatusUpdate;
        const updateInterval = attrs.statusUpdateInterval ? attrs.statusUpdateInterval : 5000;
        const defaultMessage = attrs.statusUpdateDefaultMessage ? attrs.statusUpdateDefaultMessage : '';
        const updateCompletedGrowlMessage = attrs.statusUpdateCompletedGrowlMessage ? attrs.statusUpdateCompletedGrowlMessage : null;

        const updateImmediate = attrs.statusUpdateImmediate ? (attrs.statusUpdateImmediate = (attrs.statusUpdateImmediate === 'true')) : true;

        scope.status_update_message = defaultMessage;
        scope.update_in_progress = false;

        // these are backend statuses when we won't clear interval - this means that requests will be continued
        // for all other statuses - interval will be cleared and requesting the backend will be stopped

        const validStatuses = ['pending', 'progress'];

        return scope.$watch(attrs.statusUpdateConditions, (newVal, oldVal) => {
          scope.update_in_progress = true;

          const doGetRequest = immediate =>

            Api.sendGet(statusUpdateUrl).then((res) => {
              const { data } = res;

              const { status } = data;
              const { message } = data;

              scope.status_update_message = message;
              scope.$emit('dp-status-update', data);

              if (validStatuses.indexOf(status) === -1) {
                if (!immediate && updateCompletedGrowlMessage && (status === 'completed')) { Growl.success(updateCompletedGrowlMessage); }
                scope.update_in_progress = false;
                return clearInterval(interval);
              }
            })
          ;

          var interval = setInterval(doGetRequest, updateInterval);

          if (updateImmediate) { return doGetRequest(true); }
        });
      }
    })

  ];

  return Admin_Main_Directive_DpStatusUpdate;
});
