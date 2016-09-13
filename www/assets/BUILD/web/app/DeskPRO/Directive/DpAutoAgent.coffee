define ['angular'], (angular) ->
  (DataService) ->

    groups = []
    teams  = []
    usergroups = []
    DataService.get('AgentGroups').all().then (_groups) ->
      _groups.map (group) -> groups.push {value: group.id.toString(), label: group.title}
    DataService.get('AgentTeams').all().then (_teams) ->
      _teams.map (team) -> teams.push {value: team.id.toString(), label: team.name}
    DataService.get('UserGroups').all().then (_groups) ->
      _groups.map (group) -> usergroups.push {value: group.id.toString(), label: group.title} if group.sys_name != 'everyone' && group.sys_name != 'registered'

    restrict: 'E'
    replace: true
    scope:
      ngModel: '='
    template: """
      <div class="form-group">
        <div class="col-lg-4">
          <h3>Auto Agent</h3>
        </div>
        <div class="col-lg-8">
          <label>
            <input type="checkbox" ng-model="ngModel.auto_agent" />
            Automatically create agents if they do not already exist
          </label>

          <div ng-show="ngModel.auto_agent">
            <a href="#" ng-click="$event.preventDefault(); addAction()">
              Add Action
            </a>
            <div ng-repeat="action in ngModel.actions" style="">
              <select ng-model="action.type">
                <option value="AddToPermissionGroup">Add to agent permission group</option>
                <option value="AddToTeam">Add to agent team</option>
                <option value="AddToUsergroup">Add to usergroup</option>
              </select>

              <select
                ng-if="action.type === 'AddToPermissionGroup'"
                ng-model="action.data"
                ng-options="obj.value as obj.label for obj in groups"
                ng-required="true"
                >
              </select>

              <select
                ng-if="action.type === 'AddToTeam'"
                ng-model="action.data"
                ng-options="obj.value as obj.label for obj in teams"
                ng-required="true"
                >
              </select>

              <select
                ng-if="action.type === 'AddToUsergroup'"
                ng-model="action.data"
                ng-options="obj.value as obj.label for obj in usergroups"
                ng-required="true"
                >
              </select>

              <label>
                <input type="checkbox" ng-model="action.filter_enabled" />
                only if
              </label>
              <input type="text" class="form-control" ng-model="action.filter" ng-if="action.filter_enabled" style="width: 70px;" />

              <a href="#" ng-click="$event.preventDefault(); removeAction(action)">
                x
              </a>
            </div>
          </div>
        </div>
      </div>
    """

    controller: ($scope) ->
      $scope.addAction = ->
        $scope.ngModel.actions = $scope.ngModel.actions || []
        data = if groups[0]? then groups[0].value else null
        $scope.ngModel.actions.push {type: 'AddToPermissionGroup', data: data}
      $scope.removeAction = (action) ->
        $scope.ngModel.actions = $scope.ngModel.actions || []
        index = $scope.ngModel.actions.indexOf action
        $scope.ngModel.actions.splice(index, 1) if index != -1

    link: ($scope) ->
      $scope.groups = groups
      $scope.teams  = teams
      $scope.usergroups = usergroups
