define ['angular'], (angular) ->
  (DataService) ->

    agentGroups = []
    teams  = []
    userGroups = []
    DataService.get('AgentGroups').all().then (_groups) ->
      _groups.map (group) -> agentGroups.push {value: group.id.toString(), label: group.title}
    DataService.get('AgentTeams').all().then (_teams) ->
      _teams.map (team) -> teams.push {value: team.id.toString(), label: team.name}
    DataService.get('UserGroups').all().then (_groups) ->
      _groups.map (group) -> userGroups.push {value: group.id.toString(), label: group.title} if group.sys_name != 'everyone' && group.sys_name != 'registered'

    restrict: 'E'
    replace: true
    scope:
      ngModel: '='
      title: '@title'
    template: """
      <div class="form-group">
        <div class="col-lg-4">
          <h3>{{ title }}</h3>
        </div>
        <div class="col-lg-8">
          <a href="#" ng-click="$event.preventDefault(); addAction()">
            Add Action
          </a>
          <div ng-repeat="action in ngModel" style="">
            <select ng-model="action.type">
              <option value="AddToAgentGroup" ng-if="allowedActions['AddToAgentGroup']">
                Add to agent permission group
              </option>
              <option value="AddToTeam" ng-if="allowedActions['AddToTeam']">
                Add to agent team
              </option>
              <option value="AddToUserGroup" ng-if="allowedActions['AddToUserGroup']">
                Add to usergroup
              </option>
            </select>

            <select
              ng-if="action.type === 'AddToAgentGroup'"
              ng-model="action.data"
              ng-options="obj.value as obj.label for obj in agentGroups"
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
              ng-if="action.type === 'AddToUserGroup'"
              ng-model="action.data"
              ng-options="obj.value as obj.label for obj in userGroups"
              ng-required="true"
              >
            </select>

            <label>
              <input type="checkbox" ng-model="action.filter_enabled" ng-change="changeFilter(action)" />
              only if
            </label>
            <input type="text" class="form-control" ng-model="action.filter" ng-if="action.filter_enabled" style="width: 70px;" />

            <a href="#" ng-click="$event.preventDefault(); removeAction(action)">
              x
            </a>
          </div>
        </div>
      </div>
    """

    controller: ($scope) ->
      $scope.ngModel = $scope.ngModel || []
      $scope.addAction = ->
        action = if $scope.defaultAction then {type: $scope.defaultAction} else {}
        $scope.ngModel.push action
      $scope.removeAction = (action) ->
        index = $scope.ngModel.indexOf action
        $scope.ngModel.splice(index, 1) if index != -1
      $scope.changeFilter = (action) ->
        action.filter = '' if !action.filter_enabled


    link: ($scope, $el, $attr) ->
      $scope.agentGroups = agentGroups
      $scope.teams  = teams
      $scope.userGroups = userGroups
      $scope.allowedActions = {}

      _actions = $scope.$parent.$eval($attr.actions) || []
      _actions.map (action) -> $scope.allowedActions[action] = true
      if _actions[0] then $scope.defaultAction = _actions[0]
