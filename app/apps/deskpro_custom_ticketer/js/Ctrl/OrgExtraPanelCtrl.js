define(function() {
    return ['$scope', '$org', '$http', '$el', '$app', '$timeout', 'containerElementId', function($scope, $org, $http, $el, $app, $timeout, containerElementId) {

        $scope.tab = {'title': ''};

        if (!DP_CHECK_ORG_IS_OPERATOR($org)) {
            $timeout(function() {
                $('li[data-tab-for="#' + containerElementId + '"]').hide();
            }, 25);
            return;
        }

        $scope.tab.title = 'Extra';

        var fid = $app.getSetting('ticket_operator_title_fid');
        var orgName = $org.name;

        $scope.loadOperatorTickets = function() {
            var postData = [
                {name: 'search_status[]', value: 'awaiting_agent'},
                {name: 'search_status[]', value: 'awaiting_user'},
                {name: 'search_status[]', value: 'resolved'},
                {name: 'search_status[]', value: 'archived'},
                {name: 'terms[0][type]', value: 'ticket_field['+fid+']'},
                {name: 'terms[0][op]', value: 'is'},
                {name: 'terms[0][custom_fields][field_'+fid+']', value: orgName}
            ];
            DeskPRO_Window.loadListPane(
                BASE_PATH+'agent/ticket-search/custom-filter/run',
                {postData: postData}
            );
        };
    }];
});
