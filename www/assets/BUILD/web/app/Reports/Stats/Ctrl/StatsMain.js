define([], () => [
  '$scope', '$state',
  function($scope, $state) {
    $scope.loaded = false;

    let routePath = $state.current.url;
    if (routePath[0] !== '/') {
      routePath = `/${routePath}`;
    }

    const parts = routePath.split('/');

    for (let index = 0; index < parts.length; index++) {
      const part = parts[index];
      if (part.indexOf(':') !== -1) {
        const newPart = part.replace(/(\{|\})/, () => '');
        const params = newPart.split(':');
        if($state.params[params[0]]) {
          parts[index] = $state.params[params[0]];
        }
      }
    }



    routePath = parts.join('/');
    if (routePath.indexOf('/stats') === -1) {
      routePath = `/stats${routePath}`;
    }

    const reactProps = {
      routePath
    };

    return window.ReportBundle.render(reactProps, document.getElementById('report_react_component'));
  }
  ] );