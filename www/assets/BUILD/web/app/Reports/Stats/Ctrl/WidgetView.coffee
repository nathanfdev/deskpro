define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$stateParams', '$q', 'Api',
  ($scope, $stateParams, $q, Api) ->
    widget_id = parseInt($stateParams.widget_id)
    $scope.widget = { query_parts: {}, variables: []}
    $scope.groupParams = {}
    $scope.fieldTypes = {}

    if(widget_id)
      Api.sendGet('/reports/widget/' + widget_id).then((r) ->
        $scope.widget = r.data.widget
      )

    Api.sendGet('/reports/widget/group-params').then((r) ->
      $scope.groupParams = r.data
      for key, config of r.data
        $scope.fieldTypes[key] = Object.keys r.data[key]
    )

    $scope.editor_conf = (conf = {}) ->
      opts = {
        useWrapMode: true,
        showGutter: false,
        mode: conf.mode || 'sql',

        onLoad: (ed) ->
          ed.setAutoScrollEditorIntoView(true)
          ed.setTheme("ace/theme/xcode");
          ed.setOption("minLines", conf.minLines || 3)
          ed.setOption("maxLines", 25)
          ed.setShowPrintMargin(false)

          if conf.fontFamily
            ed.setOption('fontFamily', conf.fontFamily)
          if conf.fontSize
            ed.setOption('fontSize', conf.fontSize)
      }

      return opts

    $scope.saveWidget = (event) ->
      event.preventDefault()
      postData = {
        title: $scope.widget.title
        description: $scope.widget.description
        display_types: $scope.widget.query_parts.display
        variables: $scope.widget.variables
      }
      if(widget_id)
        promise = Api.sendPostJson('/reports/widget/' + $scope.widget.id, {report: postData, parts: $scope.widget.query_parts})
      else
        promise = Api.sendPutJson('/reports/widget', {report: postData, parts: $scope.widget.query_parts})

    $scope.addVariable = () ->
      $scope.widget.variables.push({'name': 'new var', 'type': 'date', 'table': '', 'default': '', 'field_type': ''})

    $scope.deleteVariable = (index) ->
      $scope.widget.variables.splice(index, 1)
  ]
