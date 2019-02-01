define(['DeskPRO/Util/Arrays'], Arrays => [
  '$scope', '$stateParams', '$q', 'Api', 'Api2',
  function($scope, $stateParams, $q, Api, Api2) {
    const widget_id = parseInt($stateParams.widget_id);
    $scope.widget = { query_parts: {}, variables: []};
    $scope.groupParams = {};
    $scope.fieldTypes = {};

    if(widget_id) {
      Api.sendGet(`/reports/widget/${widget_id}`).then(r => $scope.widget = r.data.widget);
    }

    Api2.sendGet('/report_widgets/group-params').then(function(r) {
      $scope.groupParams = r.data;
      return (() => {
        const result = [];
        for (let key in r.data) {
          const config = r.data[key];
          result.push($scope.fieldTypes[key] = Object.keys(r.data[key]));
        }
        return result;
      })();
    });

    $scope.editor_conf = function(conf) {
      if (conf == null) { conf = {}; }
      const opts = {
        useWrapMode: true,
        showGutter: false,
        mode: conf.mode || 'sql',

        onLoad(ed) {
          ed.setAutoScrollEditorIntoView(true);
          ed.setTheme("ace/theme/xcode");
          ed.setOption("minLines", conf.minLines || 3);
          ed.setOption("maxLines", 25);
          ed.setShowPrintMargin(false);

          if (conf.fontFamily) {
            ed.setOption('fontFamily', conf.fontFamily);
          }
          if (conf.fontSize) {
            return ed.setOption('fontSize', conf.fontSize);
          }
        }
      };

      return opts;
    };

    $scope.saveWidget = function(event) {
      let promise;
      event.preventDefault();
      const postData = {
        title: $scope.widget.title,
        description: $scope.widget.description,
        display_types: $scope.widget.query_parts.display,
        variables: $scope.widget.variables
      };
      if(widget_id) {
        return promise = Api.sendPostJson(`/reports/widget/${$scope.widget.id}`, {report: postData, parts: $scope.widget.query_parts});
      } else {
        return promise = Api.sendPutJson('/reports/widget', {report: postData, parts: $scope.widget.query_parts});
      }
    };

    $scope.addVariable = () => $scope.widget.variables.push({'name': 'new var', 'type': 'date', 'table': '', 'default': '', 'field_type': ''});

    return $scope.deleteVariable = index => $scope.widget.variables.splice(index, 1);
  }
  ] );
