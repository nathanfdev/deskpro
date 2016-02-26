define [
  'Admin/Main/Ctrl/Base'
  'angular'
], (
  Admin_Ctrl_Base
  angular
  ) ->

  class Admin_ApiKeys_Ctrl_LogsView extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ApiKeys_Ctrl_LogsView'
    @CTRL_AS = 'ViewCtrl'
    @DEPS = ['$stateParams']

    init: ->
      @service = @DataService.get 'ApiLogs'
      @model = {}

    ###
    # Loads the list
    ###
    initialLoad: ->
      @service.get(@$stateParams.id).then( (model) =>
        @model = model if model
        @service.loadLog(@$stateParams.id).then( (model) =>
          @model = model
        )
      )

    getResponseData: ->
      return angular.toJson(@model.response_data, true)

    getRequestData: ->
      return angular.toJson(@model.request_data, true)

    replay: ->
      @service.replay(@model, 'subrequest').then((model) =>
        @$modal.open({
          templateUrl: @getTemplatePath('ApiLogs/replay-modal.html'),
          size: 'lg',
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

            $scope.model = model

            $scope.getResponseData = ->
              return angular.toJson($scope.model.response_data, true)

            $scope.getRequestData = ->
              return angular.toJson($scope.model.request_data, true)

            $scope.dismiss = ->
              $modalInstance.dismiss()
          ]
        });
      )


  Admin_ApiKeys_Ctrl_LogsView.EXPORT_CTRL()