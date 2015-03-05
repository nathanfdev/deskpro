define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Labels_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Labels_Ctrl_Edit'
    @CTRL_AS = 'LabelsEdit'
    @DEPS = ['em', '$stateParams', '$rootScope', 'LabelDefinition']

    init: ->
      @type = @$state.current.data.type
      @endpoint = '/labels/definitions'
      @$scope.isNew = true if !@$stateParams.label
      @definition = null
      @$scope.picker = false
      @$scope.colors = [
        '#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7',
        '#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'
      ]
      @$scope.form = {label: '', color: "", label_type: @type}
      @$scope.startDelete = => @startDelete()

    state: (to) ->
      to = '.' + to if to
      @$state.current.name.replace /(.+)\.edit|\.create$/, '$1' + to

    initialLoad: ->
      if @$stateParams.label
        @LabelDefinition.get(@type, @$stateParams.label).then (def) =>
          console.log(def)
          return if !def
          @definition = def
          @$scope.form = angular.copy def

    color2hex: (color) ->
      color = color.replace /\s/g, ''
      if /^#?[0-9A-F]{3}$/i.test(color) or /^#?[0-9A-F]{6}$/i.test(color)
        color = '#' + color if not color.match(/^#/)
        return color

      rgb = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/)
      if not rgb then return '#ffffff'
      hex = (x) ->
        return("0" + parseInt(x).toString(16)).slice(-2)

      return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);

    saveLabel: ->
      return false if not @$scope.form.label
      return false if @definition && @definition.label == @$scope.form.label && @definition.color == @$scope.form.color

      dummy = $('<div></div>').css 'color', @color2hex(@$scope.form.color)
      color = @color2hex(dummy.css 'color')
      @$scope.form.color = color

      @startSpinner 'saving_label'

      if @definition
        sendData = {old: @definition || {}, new: @$scope.form}
        method = 'sendPutJson'
      else
        sendData = @$scope.form
        method = 'sendPostJson'

      @Api[method](@endpoint, sendData)

      .success (data) =>
        @stopSpinner('saving_label', true).then =>
          @Growl.success @getRegisteredMessage 'saved_label'

        @LabelDefinition.update @definition, data
        @definition = data

        if @$scope.isNew
          @$state.go @state('gocreate')
        else
          @$state.go @state('edit'), {label: data.label}

      .error =>
        @Growl.error @getRegisteredMessage 'not_saved_label'

      .finally =>
        @stopSpinner 'saving_label', true



    startDelete: () ->
      return if !@definition

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Labels/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then =>
        @Api.sendDelete @endpoint, @definition

        .success =>
          @LabelDefinition.remove @definition
          @$state.go @state ''

        # todo
        .error =>
          @$state.go @state ''

        # todo
        .finally =>
          @$state.go @state ''



  Admin_Labels_Ctrl_Edit.EXPORT_CTRL()