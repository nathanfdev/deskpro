define(function() {
    return ['$scope', '$org', '$http', '$el', '$app', '$timeout', 'containerElementId', function($scope, $org, $http, $el, $app, $timeout, containerElementId) {

        $scope.tab = {'title': ''};

        if (!DP_CHECK_ORG_IS_OPERATOR($org)) {
            $timeout(function() {
                $('li[data-tab-for="#' + containerElementId + '"]').hide();
            }, 25);
            return;
        }

        $scope.tab.title = 'Operator Tickets';

        var fid = $app.getSetting('ticket_operator_title_fid');
        var orgName = $org.name;

        var postData = [
            {name: 'search_status[]', value: 'awaiting_agent'},
            {name: 'search_status[]', value: 'awaiting_user'},
            {name: 'search_status[]', value: 'resolved'},
            {name: 'search_status[]', value: 'archived'},
            {name: 'terms[0][type]', value: 'ticket_field['+fid+']'},
            {name: 'terms[0][op]', value: 'is'},
            {name: 'terms[0][custom_fields][field_'+fid+']', value: orgName}
        ];

        $scope.loading = true;
        $.ajax({
            url: BASE_PATH+'agent/ticket-search/custom-filter/run?view_type=json',
            data: postData,
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                console.log(data.tickets);
                $scope.loading = false;
                $scope.tickets = data.tickets;
                $scope.$apply();
            }
        });
    }];
});
