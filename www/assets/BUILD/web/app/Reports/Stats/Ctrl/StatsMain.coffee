define [], () -> [
  '$scope', '$state',
  ($scope, $state) ->
    $scope.loaded = false

    routePath = $state.current.url;
    if (routePath[0] != '/')
      routePath = '/' + routePath;

    reactProps = {
      routePath: routePath
    }

    window.ReportBundle.render(reactProps, document.getElementById('report_react_component'));
  ]