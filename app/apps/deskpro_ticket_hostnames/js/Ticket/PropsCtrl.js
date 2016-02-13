define(function() {
  return ['$app', '$scope', '$ticket', '$http', '$q', function($app, $scope, $ticket, $http, $q) {
    $http.get($app.getRequestHandlerUrl('agent', 'get-hostnames', { ticket_id: $ticket.id }), {
      responseType: 'json'
    }).success(function(data) {
      $scope.user_list = data.user_list;
    });
  }];
});