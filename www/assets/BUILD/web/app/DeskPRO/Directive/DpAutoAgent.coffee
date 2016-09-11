define ['angular'], (angular) ->
  (DataService) ->

    groups = []
    DataService.get('AgentGroups').all().then (_groups) ->
      _groups.map (group) -> groups.push {value: group.id.toString(), label: group.title}

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
            Add agents to this permission group
            <select
                ng-model="ngModel.auto_agent_permission_group"
                ng-options="obj.value as obj.label for obj in groups"
                ng-required="true"
                >
            </select>
          </div>
        </div>
      </div>
    """

    link: ($scope) ->
      $scope.groups = groups
