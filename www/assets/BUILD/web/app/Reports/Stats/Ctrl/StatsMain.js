define [], () -> [
  '$scope', '$state',
  ($scope, $state) ->
    $scope.loaded = false

    routePath = $state.current.url;
    if (routePath[0] != '/')
      routePath = '/' + routePath;

    parts = routePath.split('/');

    for part, index in parts
      if part.indexOf(':') != -1
        newPart = part.replace(/(\{|\})/, () -> '');
        params = newPart.split(':')
        if($state.params[params[0]])
          parts[index] = $state.params[params[0]]



    routePath = parts.join('/')
    if routePath.indexOf('/stats') == -1
      routePath = '/stats' + routePath

    reactProps = {
      routePath: routePath
    }

    window.ReportBundle.render(reactProps, document.getElementById('report_react_component'));
  ]